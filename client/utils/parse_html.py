# -*- coding:utf-8 -*-

from homepageParser import HomepageParser
from tagParser import TagParser
import json


def get_home_page_dict(content, url):
    try:
        parser = HomepageParser()
        for one in parser.parse_homepage(content, url):
            return json.dumps(one)
    except Exception,e:
        print(e)
        return ""

def get_tag_dict(content, url):
    try:
        t = TagParser()
        rs = t.get_user_tag(content, url)
        if rs:
            return json.dumps(rs)
        return ""
    except Exception,e:
        print(e)
        return ""
