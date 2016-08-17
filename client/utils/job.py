# -*- coding:utf-8 -*-
# coding:utf-8
import demjson
import time
import json
import re
from utils import config
from utils import http
from utils import cache
from utils import log

logger = log.logger


def start_get_all_job():
    print("开始新一轮任务")
    url = config.job_api["get_all_job"]

    # 查看老的设备号,如果存在,就复用
    old_data = cache.load_data()
    if old_data and old_data["config"] and old_data["config"]["device_id"]:
        url += "&device_id=" + old_data["config"]["device_id"]

    json = http.get_job_content(url)
    obj = demjson.decode(json)

    if obj["config"]["cookie"] == "":
        print("cookie 为空,退出")
        return

    if obj["config"]["device_id"] == "":
        print("device_id 为空,退出")
        return
    if "sleep_time" in obj["config"]:
        config.request_sleep_time = int(obj["config"]["sleep_time"])

    cookie = obj["config"]["cookie"]
    device_id = obj["config"]["device_id"]
    for type_list in obj["url_list"]:
        is_json = False

        if type_list in config.has_next_page_type:
            is_json = True

        while len(obj["url_list"][type_list]) != 0:
            one_url = obj["url_list"][type_list][0]

            time.sleep(config.request_sleep_time)
            try:
                dt = http.get_weibo_content(one_url, cookie, is_json)
                dt["type"] = type_list
                dt["url"] = one_url
                dt["device_id"] = device_id
                report_one_job = http.send_data(config.job_api["done_one_job"], dt)
                if report_one_job == "ok":
                    print("url: " + one_url + "   get ok.code:" + dt["code"])

                    if dt["code"] == "200":
                        next_url = is_has_next_page(one_url, type_list, dt["data"])

                        if next_url != "" and next_url != "error_json":
                            obj["url_list"][type_list].append(next_url)

                    del obj["url_list"][type_list][0]
                    cache.save_data(obj)

            except Exception as  e:
                print(e)
                logger.error(e.message)
                logger.error(one_url)


def restore_last_job():
    print("恢复上次的任务")
    obj = cache.load_data()
    if not obj or "config" not in obj:
        return

    if obj["config"]["cookie"] == "":
        print("cookie 为空,退出")
        return

    if obj["config"]["device_id"] == "":
        print("device_id 为空,退出")
        return
    if "sleep_time" in obj["config"]:
        config.request_sleep_time = int(obj["config"]["sleep_time"])

    cookie = obj["config"]["cookie"]
    device_id = obj["config"]["device_id"]
    for type_list in obj["url_list"]:
        is_json = False

        if type_list in config.has_next_page_type:
            is_json = True

        while len(obj["url_list"][type_list]) != 0:
            one_url = obj["url_list"][type_list][0]

            time.sleep(config.request_sleep_time)
            try:
                dt = http.get_weibo_content(one_url, cookie, is_json)
                dt["type"] = type_list
                dt["url"] = one_url
                dt["device_id"] = device_id
                report_one_job = http.send_data(config.job_api["done_one_job"], dt)
                if report_one_job == "ok":
                    print("url: " + one_url + "   get ok.code:"+dt["code"])

                    if dt["code"] == "200":
                        next_url = is_has_next_page(one_url, type_list, dt["data"])

                        if next_url != "" and next_url != "error_json":
                            obj["url_list"][type_list].append(next_url)

                    del obj["url_list"][type_list][0]
                    cache.save_data(obj)

            except Exception as  e:
                print(e)
                logger.error(e.message)
                logger.error(one_url)


# 对特殊的页面进行处理,判断是否还有下一页进行抓取,如果要抓取,返回下一页
def is_has_next_page(url, url_type, content):
    if url_type not in config.has_next_page_type:
        return ""
    try:
        obj = json.loads(content)

        if url_type == u"weibo_list":
            # 不爬大v
            if obj["count"] > config.max_weibo_number:
                return ""
            if obj["cards"][0]["mod_type"] == "mod/pagelist":
                next_page_url = _get_next_page_url(url)
                return next_page_url
            else:
                return ""

        if url_type == u"pinglun_list":
            # 不爬大v
            if "maxPage" in obj[-1] and obj[-1]["maxPage"] > (config.max_pinglun_number / 10):
                return ""

            if obj[-1]["mod_type"] == "mod/pagelist":
                next_page_url = _get_next_page_url(url)
                return next_page_url
            else:
                return ""

        if url_type == u"zhuanfa_list":
            # 不爬大v
            if "maxPage" in obj[-1] and obj[-1]["maxPage"] > (config.max_zhuanfa_number / 10):
                return ""

            if obj[-1]["mod_type"] == "mod/pagelist":
                next_page_url = _get_next_page_url(url)
                return next_page_url
            else:
                return ""

        if url_type == u"relation_list":
            if obj["count"]:
                return _get_next_page_url(url)

            return ""
    except Exception, e:
        print(e)
        print("error_json try again")
        # 说明被屏蔽了
        return "error_json"


# 汇报不能完成的任务
def finish_all_job():
    obj = cache.load_data()
    if not obj or "config" not in obj or obj["config"]["device_id"] == "":
        print("device_id 为空,退出")
        return

    dt = {}
    dt["device_id"] = obj["config"]["device_id"]
    dt["data"] = {}
    for type_list in obj["url_list"]:
        dt["data"][type_list] = obj["url_list"][type_list]

    dt["data"] = json.dumps(dt["data"]);
    for i in range(1,4):
        report_finish_job = http.send_data(config.job_api["done_all_job"],dt)

        if report_finish_job == "ok":
            break

# 获取下一页的页码数
def _get_next_page_url(url):
    m = re.match(r".+=(\d+)$", url)
    if m:
        old_page = m.group(1)
        return url[:0 - len(old_page)] + str(int(old_page) + 1)
    else:
        return ""
