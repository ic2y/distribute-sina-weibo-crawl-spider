# -*- coding:utf-8 -*-
# coding:utf-8

from utils import job
# 1.load cache 继续上次没有完整的任务
# 2.开始无穷循环,从服务器拉取任务,执行任务

if __name__ == "__main__":
    job.restore_last_job()
    job.finish_all_job()
    while True:
        job.start_get_all_job()
        job.finish_all_job()