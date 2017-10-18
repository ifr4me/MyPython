# coding=utf-8
import requests,json,time
import logging

LOG_FILE = '/var/log/wucaicheng.log'
#LOG_FILE = 'd:\\wucaicheng.log'


#If find date in LOG_FILE, exit. so you need set cron execute twice everyday, when the server is not available.
date = time.strftime("%Y-%m-%d", time.localtime())
print date
log = open(LOG_FILE)
try:
    all_log = log.read()
    result = all_log.find(date)
    print result
    if result > 0 :
        exit()
finally:
    log.close()


# prepare login and sign
headers = {'User-Agent':'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36',
        'X - Requested - With':'XMLHttpRequest',
        'Origin':'http://bj.wucaicheng.com.cn'}

wucaichengUrl = "http://bj.wucaicheng.com.cn/html/member/api/1/login"
signUrl = 'http://bj.wucaicheng.com.cn/html/member/api/1/sign'
postData = {'type':'2','phone':'18611111111','phonecode':'','password':'111111'}


log_level = logging.DEBUG
logger = logging.getLogger("loggingmodule.NomalLogger")
handler = logging.FileHandler(LOG_FILE)
formatter = logging.Formatter("[%(asctime)s]%(message)s")
#formatter = logging.Formatter("[%(levelname)s][%(funcName)s][%(asctime)s]%(message)s")
handler.setFormatter(formatter)
logger.addHandler(handler)
logger.setLevel(log_level)
#logger.info("this is a info msg!")
# request = urllib2.Request(wucaichengurl ,data=postData ,headers = headers)
# response  = urllib2.urlopen(url = wucaichengurl, data=urllib.urlencode(postData))
# print response .read()

print time.strftime("%Y-%m-%d %H:%M:%S", time.localtime())
s = requests.session()
login = s.post(wucaichengUrl, data=postData, headers=headers)
print login.content
response = s.post(signUrl, cookies=login.cookies, headers = headers)
print response.content
decode = json.loads(response.content)
print decode['meta']['msg']
msg = '%s\n%s\n%s\n' % (login.content , response.content , decode['meta']['msg'])
#print msg
logger.info(msg=msg)

s.close()
