#!/usr/bin/python
#coding:utf-8

import sys, os, time, string
import requests
from upload import *
from paohai_log import *

def main():
    while 1:
        current_hour = time.strftime('%H', time.localtime(time.time()))

        # refresh access token every hour
        r = requests.get('http://paohai.ikamobile.com/wechat/refreshaccesstoken')
        write_log('AccessToken: %s' % (r.text))
        # print r.text

        if (current_hour == '01'): # upload pictures to pan.baidu.com at 01:00AM every day
            syncInNewThread()
        time.sleep(3600) # one hour

if __name__ == "__main__":

    # daemon_num = os.system('ps -fe | grep "python" | grep "paohai-daemon" | grep -v "grep" ')
    daemon_num = os.popen('ps -fe | grep "python" | grep "paohai-daemon" | grep -v "grep" | wc -l').read().strip()
    if int(daemon_num) > 1:
        print "daemon already running. exit."
        sys.exit(0)

    # do the UNIX double-fork magic, see Stevens' "Advanced 
    # Programming in the UNIX Environment" for details (ISBN 0201563177)
    try: 
        pid = os.fork() 
        if pid > 0:
            # exit first parent
            sys.exit(0) 
    except OSError, e: 
        print >>sys.stderr, "fork #1 failed: %d (%s)" % (e.errno, e.strerror) 
        sys.exit(1)


    # decouple from parent environment
    # os.chdir("/") don't change current working path because will visit postcards folder using relative path
    os.setsid() 
    os.umask(0) 
    # do second fork
    try: 
        pid = os.fork() 
        if pid > 0:
            # exit from second parent, print eventual PID before
            print "Daemon PID %d" % pid 
            sys.exit(0) 
    except OSError, e: 
        print >>sys.stderr, "fork #2 failed: %d (%s)" % (e.errno, e.strerror) 
        sys.exit(1) 
    # start the daemon main loop
    main() 
