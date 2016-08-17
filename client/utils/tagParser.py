#!/usr/bin/env python
# coding:utf-8
import re
from lxml import etree
import sys

reload(sys)
sys.setdefaultencoding('utf8')

class TagParser():
    def __init__(self):
        pass

    def get_user_tag(self, content, url):
        soup = etree.HTML(content)
        tags = ""
        user_id = self.get_uid_from_response(url)

        list_a1 = soup.xpath("//html/body/div[5]/a")
        list_a2 = soup.xpath("//html/body/div[6]/a")

        list = None
        if len(list_a1) >= len(list_a2):
            list = list_a1
        else:
            list = list_a2

        if len(list) != 0:
            for a in list:
                tag = a.text
                if tags == "":
                    tags = tag
                else:
                    tags += "," + tag

            if not tags:
                return None
            tagsItem = {}
            tagsItem["user_id"] = user_id
            tagsItem["tags"] = tags.encode("utf-8", "ignore")
            return tagsItem
        return None

    def get_uid_from_response(self, url):
        pattern = re.compile(r'(\d+)')
        res = re.findall(pattern, url)
        id = 0
        if res:
            id = int(res[0])
            # print "id:", id
        return id

    def print_node(self, node):
        print "====================================="
        for key, value in node.items():
            print "%s:%s" % (key, value)
        for subnode in node.getchildren():
            print "%s:%s" % (subnode.tag, subnode.text)