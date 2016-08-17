# -*- coding:utf-8 -*-
import random

# 请求的间隔时间
request_sleep_time = 7

# 有下一页的类型
has_next_page_type = [u"weibo_list", u"pinglun_list", u"zhuanfa_list", u"relation_list"]

# 大v发送的垃圾微博太多,大于这个值的认为是大v,不爬
max_weibo_number = 10000
max_pinglun_number = 100
max_zhuanfa_number = 100

job_api = {"get_all_job": "http://xxx.xx/index.php?action=get_all_job",
           "done_one_job": "http://xxx.xx/index.php?action=done_one_job",
           "done_all_job": "http://xxx.xx/index.php?action=done_all_job",
           }


# 设定浏览器请求的header头,随机从中选取一个
header_user_agent = ["Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_8; en-us) AppleWebKit/534.50 (KHTML, like Gecko) "
                     "Version/5.1 Safari/534.50",

                     "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-us) AppleWebKit/534.50 (KHTML, like Gecko) "
                     "Version/5.1 Safari/534.50",

                     "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0;",

                     "Opera/9.80 (Macintosh; Intel Mac OS X 10.6.8; U; en) Presto/2.8.131 Version/11.11",

                     "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Maxthon 2.0)",

                     "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; 360SE)",

                     "Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_3_3 like Mac OS X; en-us) AppleWebKit/533.17.9"
                     " (KHTML, like Gecko) Version/5.0.2 Mobile/8J2 Safari/6533.18.5",

                     "Mozilla/5.0 (iPad; U; CPU OS 4_3_3 like Mac OS X; en-us) AppleWebKit/533.17.9 "
                     "(KHTML, like Gecko) Version/5.0.2 Mobile/8J2 Safari/6533.18.5",


                     "Mozilla/5.0 (Linux; U; Android 2.3.7; en-us; Nexus One Build/FRF91) "
                     "AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1",

                     "Opera/9.80 (Android 2.3.4; Linux; Opera Mobi/build-1107180945; U; en-GB) "
                     "Presto/2.8.149 Version/11.10",


                     "Mozilla/5.0 (BlackBerry; U; BlackBerry 9800; en) AppleWebKit/534.1+ "
                     "(KHTML, like Gecko) Version/6.0.0.337 Mobile Safari/534.1+",

                     "Mozilla/5.0 (SymbianOS/9.4; Series60/5.0 NokiaN97-1/20.0.019; Profile/MIDP-2.1 "
                     "Configuration/CLDC-1.1) AppleWebKit/525 (KHTML, like Gecko) BrowserNG/7.1.18124",

                     "NOKIA5700/ UCWEB7.0.2.37/28/999",

                     "Openwave/ UCWEB7.0.2.37/28/999",

                     "ozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) "
                     "Ubuntu Chromium/50.0.2661.102 Chrome/50.0.2661.102 Safari/537.36"]


def get_one_random_header():
    return header_user_agent[random.randint(0, len(header_user_agent) - 1)]
