#!/usr/bin/python
#coding:utf-8

import sys, os, time, threading
from sendmail import *
from paohai_log import *

def sync():
    os.system('nohup python bypy.py syncup ../../userdata/payed/ &');
    current_time = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time()))
    send_mail(u'泡海明信片已同步至百度云', current_time)
    write_log('syncup done.')

def syncInNewThread():
    t = threading.Thread(target=sync)
    t.start()

if __name__ == "__main__":
    syncInNewThread()