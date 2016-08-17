#!/usr/bin/env python
# coding:utf-8
import re
from bs4 import BeautifulSoup
from utils import log
import sys

reload(sys)
sys.setdefaultencoding('utf8')

logger = log.logger


class HomepageParser():
    def __init__(self):
        pass

    def parse_error(self, msg):
        logger.error("post:%s" % msg)

    def parse_homepage(self, content, url):
        for one in self.parse_info2(content, url):
            yield one

    def parse_info1(self, content, url):
        soup = BeautifulSoup(content, "lxml")
        info_tip_ele = soup.find("div", text=u"基本信息")
        uid = self.get_uid_from_response(url)
        info = {}
        if info_tip_ele:
            info_ele = info_tip_ele.next_sibling

            info_eles = info_ele.strings
            user_info = {}
            user_info["user_id"] = uid

            key = ""
            for ele in info_eles:
                if key == "":
                    key = ele
                    continue

                pos = 99999
                pos_offset = 1
                pos1 = ele.find(":")
                pos2 = ele.find("：")
                if pos1 != -1:
                    pos = pos1
                if pos2 != -1 and pos2 < pos:
                    pos = pos2
                    pos_offset = 3

                el = []
                el.append(key)
                el.append(ele[pos + pos_offset:])
                key = ""
                # el = ele.split(":")
                if len(el) == 2 and el[0] in [u"昵称", u"性别", u"地区", u"生日", u"简介", u"认证", u"认证信息"]:
                    info[el[0]] = el[1]
                    info_item = el[1].encode("utf-8")
                    if el[0] == u"昵称":
                        user_info["user_name"] = info_item
                    elif el[0] == u"性别":
                        user_info["sex"] = info_item
                    elif el[0] == u"地区":
                        region = info_item.split(" ")
                        if len(region) == 1:
                            user_info["province"] = ""
                            user_info["city"] = region[0]
                        else:
                            user_info["province"] = region[0]
                            user_info["city"] = region[1]
                    elif el[0] == u"生日":
                        if len(info_item.split("-")) < 3:
                            user_info["birthday"] = "2050-" + info_item
                        else:
                            user_info["birthday"] = info_item
                        p = re.compile(r"^\d{4}-\d{2}-\d{2}$")
                        if not p.findall(user_info["birthday"]):
                            user_info["birthday"] = ""
                    elif el[0] == u"简介":
                        user_info["abstract"] = info_item.encode("utf-8", "ignore").replace(" ", ""). \
                            replace("\n", "").replace("\xc2\xa0", "").replace("\xF0\x9F\x91\x8A", ""). \
                            replace("\xF0\x9F\x91\xBC", "").replace("\xF0\x9F\x8C\xB8\xF0\x9F", "")
                    elif el[0] == u"认证":
                        user_info["identityInfo"] = info_item.encode("utf-8", "ignore").replace(" ", ""). \
                            replace("\n", "").replace("\xc2\xa0", "").replace("\xF0\x9F\x91\x8A", ""). \
                            replace("\xF0\x9F\x91\xBC", "").replace("\xF0\x9F\x8C\xB8\xF0\x9F", "")
                    elif el[0] == u"认证信息":
                        user_info["identityDetails"] = info_item.encode("utf-8", "ignore").replace(" ", ""). \
                            replace("\n", "").replace("\xc2\xa0", "").replace("\xF0\x9F\x91\x8A", ""). \
                            replace("\xF0\x9F\x91\xBC", "").replace("\xF0\x9F\x8C\xB8\xF0\x9F", "")
            yield user_info

    def parse_info2(self, content, url):
        soup = BeautifulSoup(content, "lxml")
        info_tip_ele = soup.find("div", text=u"基本信息")
        uid = self.get_uid_from_response(url)
        info = {}
        is_crawl_tags = False
        if info_tip_ele:
            info_ele = info_tip_ele.next_sibling

            info_eles = info_ele.strings
            user_info = {}
            user_info["user_id"] = uid
            for ele in info_eles:
                pos = 99999
                pos_offset = 1
                pos1 = ele.find(":")
                pos2 = ele.find("：")
                if pos1 != -1:
                    pos = pos1
                if pos2 != -1 and pos2 < pos:
                    pos = pos2
                    pos_offset = 3

                el = []
                el.append(ele[0:pos])
                el.append(ele[pos + pos_offset:])

                # el = ele.split(":")
                if len(el) == 2 and el[0] == u"标签":
                    is_crawl_tags = True

                if len(el) == 2 and el[0] in [u"昵称", u"性别", u"地区", u"生日", u"简介", u"认证", u"认证信息"]:
                    info[el[0]] = el[1]
                    info_item = el[1].encode("utf-8")
                    if el[0] == u"昵称":
                        user_info["user_name"] = info_item
                    elif el[0] == u"性别":
                        user_info["sex"] = info_item
                    elif el[0] == u"地区":
                        region = info_item.split(" ")
                        if len(region) == 1:
                            user_info["province"] = ""
                            user_info["city"] = region[0]
                        else:
                            user_info["province"] = region[0]
                            user_info["city"] = region[1]
                    elif el[0] == u"生日":
                        if len(info_item.split("-")) < 3:
                            user_info["birthday"] = "2050-" + info_item
                        else:
                            user_info["birthday"] = info_item
                        p = re.compile(r"^\d{4}-\d{2}-\d{2}$")
                        if not p.findall(user_info["birthday"]):
                            user_info["birthday"] = ""
                    elif el[0] == u"简介":
                        user_info["abstract"] = info_item.encode("utf-8", "ignore").replace(" ", ""). \
                            replace("\n", "").replace("\xc2\xa0", "").replace("\xF0\x9F\x91\x8A", ""). \
                            replace("\xF0\x9F\x91\xBC", "").replace("\xF0\x9F\x8C\xB8\xF0\x9F", "")
                    elif el[0] == u"认证":
                        user_info["identityInfo"] = info_item.encode("utf-8", "ignore").replace(" ", ""). \
                            replace("\n", "").replace("\xc2\xa0", "").replace("\xF0\x9F\x91\x8A", ""). \
                            replace("\xF0\x9F\x91\xBC", "").replace("\xF0\x9F\x8C\xB8\xF0\x9F", "")
                    elif el[0] == u"认证信息":
                        user_info["identityDetails"] = info_item.encode("utf-8", "ignore").replace(" ", ""). \
                            replace("\n", "").replace("\xc2\xa0", "").replace("\xF0\x9F\x91\x8A", ""). \
                            replace("\xF0\x9F\x91\xBC", "").replace("\xF0\x9F\x8C\xB8\xF0\x9F", "")
            yield user_info







    def get_uid_from_response(self, url):
        pattern = re.compile(r'(\d+)')
        res = re.findall(pattern, url)
        id = 0
        if res:
            id = int(res[0])
            # print "id:", id
        return id
