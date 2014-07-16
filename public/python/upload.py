#!/usr/bin/python
#coding:utf-8

import sys, os, time, threading
from sendmail import *

def sync():
    os.system('nohup python bypy.py syncup ../../postcards/ &');

    current_time = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time()))
    f = open("/tmp/paohai-daemon-log.txt", "a")
    if send_mail(u'泡海明信片已同步至百度云', current_time):
        f.write('Notify mail sent succeed.  %s\n' % current_time)
    else:
        f.write('Notify mail send failed.  %s\n' % current_time)
    f.flush()
    f.close()

def syncInNewThread():
    t = threading.Thread(target=sync)
    t.start()

if __name__ == "__main__":
    syncInNewThread()