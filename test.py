from urlparse import urlparse
from threading import Thread
import httplib, sys
from Queue import Queue

concurrent = 200

def doWork():
    while True:
        url = q.get()
        status, url = getStatus(url)
        doSomethingWithResult(status, url)
        q.task_done()

def getStatus(ourl):
    try:
        url = urlparse(ourl)
        conn = httplib.HTTPConnection(url.netloc)
        conn.request("HEAD", url.path)
        res = conn.getresponse()
        return res.status, ourl
    except:
        return "error", ourl

def doSomethingWithResult(status, url):
    print status, url,"\n"

q = Queue(concurrent * 2)
for i in range(concurrent):
    t = Thread(target=doWork)
    t.daemon = True
    t.start()
try:
    for x in range(30):
        q.put("https://jsonplaceholder.typicode.com/todos/1")
    q.join()
except KeyboardInterrupt:
    sys.exit(1)