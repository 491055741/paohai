import sys, os, time

def write_log(msg):

    current_time = time.strftime('%m/%d %H:%M', time.localtime(time.time()))
    f = open("/tmp/quyou_daemon_log.txt", "a")
    f.write('%s  %s\n' % (current_time, msg))
    f.flush()
    f.close()

