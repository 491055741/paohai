import sys, os, time

def write_log(msg):

    current_time = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time()))
    f = open("/tmp/paohai-daemon-log.txt", "a")
    f.write('%s  %s\n' % (msg, current_time))
    f.flush()
    f.close()

