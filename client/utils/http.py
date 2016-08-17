# -*- coding:utf-8 -*-
# coding:utf-8
import urllib2
import urllib
import config
import time
import socket
from utils import parse_html
MAX_TRY_TIME = 2
debug = True
socket.setdefaulttimeout(100.0)


def get_weibo_content(url, cookie, is_json=False, try_time = 0):
    if try_time == MAX_TRY_TIME:
        return {"code": "302", "data": ""}

    request = urllib2.Request(url)
    request.add_header('User-Agent', config.get_one_random_header())
    # request.add_header('Accept-Encoding', 'gzip,deflate,sdch')
    request.add_header('Accept-Language', 'zh-CN,zh;')
    request.add_header('Referer', url)
    request.add_header('Upgrade-Insecure-Requests', '1')
    request.add_header('Cookie', cookie)
    if is_json:
        request.add_header('Accept', 'application/json, text/javascript, */*; q=0.01')
        request.add_header('Host', 'm.weibo.cn')
    else:
        request.add_header('Accept', 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8')
        request.add_header('Host', 'weibo.cn')

    try:
        response = urllib2.urlopen(request,timeout=100)
        data = response.read()
        if len(data) == 0:
            time.sleep(config.request_sleep_time)
            return get_weibo_content(url, cookie, is_json, try_time + 1)

        return {"code": "200",
                "data": data}

    except urllib2.HTTPError, e:
        print(e)
        return {"code": "302", "data": ""}


# 对于两个特殊的请求.需要特殊处理,用户的homepage页和 tag页,需要对数据进行抽取之后才能进行发送
def send_data(url, data):
    if "data" not in data or "type" not in data:
        return None

    if data["type"] == "tag_list":
        data["data"] = parse_html.get_tag_dict(data["data"],data["url"])
    if data["type"] == "home_list":
        data["data"] = parse_html.get_home_page_dict(data["data"],data["url"])

    data = urllib.urlencode(data)
    request = urllib2.Request(url)
    opener = urllib2.build_opener(urllib2.HTTPCookieProcessor())
    response = opener.open(request, data)
    rs = response.read()
    if debug:
        print("send_data rs: "+ rs)
    return rs


def get_job_content(url):
    request = urllib2.Request(url)
    response = urllib2.urlopen(request)
    return response.read()
