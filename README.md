# 0.简介

distribute sina weibo crawl spider

本分布式新浪微博爬虫可以爬取home页资料，个人标签，所有微博，评论，转发，和关注列表，并进行预处理。存放在mongodb中。

client模块是python做的分布式爬虫，需要安装demjson，bs4 ，lxml，可以放在多台不同的ip上进行爬取

server模块是纯php写的收集函数，主要负责任务发放，任务收集,需要php5环境，需要预先安装好redis扩展，mongodb扩展，版本最新即可。

tongji模块是ci框架写的统计函数，并用来显示图表的。

# 1.运行前需要修改如下东西

--------------------------------------------------------
client/utils/config.py

修改 job_api 里的xxx.xx为你自己的网站的域名

--------------------------------------------------------

server/mongoDb.php 里的 __construct() 里的 $this->manager 修改为自己的mongodb连接方式

server/redisDb.php 里的 __construct() 里修改redis连接



--------------------------------------------------------

tongji/application/config/database.php 修改mysql的数据库，导入tongji.sql

ci框架

tongji/index.php/Welcome/count 访问一次进行一次统计，我们可以用crontab 10分钟访问一次

tongji/index.php/Welcome/  显示统计的图标，用highcharts进行统计

--------------------------------------------------------


# 2.运行步骤

1.首先，将1 里面的内容修改正确。

2.在redis中创建config_cookie_list数组，向这个数组插入一系列cookie，cookie必须是访问m.weibo.cn的时候使用的cookie。
不管是使用人工的办法，还是机器的办法，需要在config_cookie_list 中写入不低于1个的cookie

3.部署client，在N台设备，IP不同，（手机，树莓派，笔记本，vps等）上部署，并用crontab定时sh脚本清理client一次。client具有断点续爬功能。

4.此时已经可以正常收集数据。

5.导入统计数据库，方便统计数据插入

6.使用crontab 或者网站检测工具都行，定时访问 tongji/index.php/Welcome/count 进行统计。

7.查看 tongji/index.php/Welcome/ 查看统计结果

--------------------------------------------------------

# 3.TIPs

1.client具有断点续爬功能，所以可以随意kill

2.server端有去重功能，而且url队列不会实时生成，只有在 队列数量不足的情况下，才会生成。

3.目前测试的结果，在6台client 6账号的情况下已经不会被新浪完全屏蔽了。如果设置的速度过快，有被新浪完全屏蔽的可能。目前的速度，一星期左右，可以爬取百万级别数据。

4.爬取数据都在mongo中存储。

5.爬取过程中，不可避免的要失败。client端不管结果是失败还是成功，都会报告给收集端。如果爬取不成功，重新加入等待队列。由某一个client重复爬取。

6.任务分配的url，默认都是第1页，然后client自动探测是否有下一页。比如探测到了第23页发生了301错误，那么第23页进入server端等待队列。分配给某个client后，继续23页开始爬取并探测下一页。
