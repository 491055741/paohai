#!/usr/bin/python
#coding:utf-8

import smtplib, sys
from email.mime.text import MIMEText

mailto_list = ["lipeng@ikamobile.com"]
mail_host   = "smtp.163.com"
mail_user   = "paohaipostcard"
mail_pass   = "paohai"
mail_postfix = "163.com"

######################
def send_mail(sub, content):
  '''
   to_list:发给谁
   sub:主题
   content:内容
   send_mail("aaa@126.com","sub","content")'''

  me  = mail_user + "<" + mail_user + "@" + mail_postfix + ">"
  msg = MIMEText(content, _charset = 'gbk')
  msg['Subject'] = sub
  msg['From'] = me
  msg['To'] = ";".join(mailto_list)

  try:
    s = smtplib.SMTP()
    s.connect(mail_host)
    s.login(mail_user,mail_pass)
    s.sendmail(me, mailto_list, msg.as_string())
    s.close()
    return True
  except Exception, e:
    print str(e)
    return False
