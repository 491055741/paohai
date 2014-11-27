#!/usr/bin/python
#coding:utf-8

import sys, os, time, threading
from sendmail import *
from quyou_log import *

def sync():
    cmdStr = 'nohup python '+os.path.dirname(os.path.abspath(__file__))+'/bypy.py syncup '+os.path.dirname(os.path.abspath(__file__))+'/../../userdata/payed/ &'
    os.system(cmdStr)
    current_time = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time()))
    send_mail(u'趣邮明信片已同步至百度云', current_time)
    write_log('syncup done.')

def syncInNewThread():
    t = threading.Thread(target=sync)
    t.start()

if __name__ == "__main__":
    syncInNewThread()