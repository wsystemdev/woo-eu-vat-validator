import json, sys, urllib.request, http.cookiejar
B="http://127.0.0.1:8099/wp-json/wc/store/v1"
def mk():
    cj=http.cookiejar.CookieJar(); op=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj)); return op,{}
def call(s,method,path,body=None):
    op,h=s
    req=urllib.request.Request(B+path,data=json.dumps(body).encode() if body is not None else None,method=method,headers={"Content-Type":"application/json",**h})
    try: r=op.open(req,timeout=60); code=r.status; data=json.loads(r.read())
    except urllib.error.HTTPError as e: code=e.code; data=json.loads(e.read() or b'{}'); r=e
    if r.headers.get("Nonce"): h["Nonce"]=r.headers["Nonce"]
    if r.headers.get("Cart-Token"): h["Cart-Token"]=r.headers["Cart-Token"]
    return code,data
def addr(country,vat):
    a={"first_name":"T","last_name":"B","address_1":"Str 1","city":"Berlin" if country=="DE" else "Budapest","postcode":"10115" if country=="DE" else "1093","country":country,"email":"t@example.com","phone":""}
    if vat is not None: a["wsevv/vat-number"]=vat
    return a
fails=0
def check(name,cond,extra=""):
    global fails
    print(("PASS " if cond else "FAIL ")+name, extra if not cond else "")
    fails+= 0 if cond else 1
def scenario(country,vat,expect_tax,expect_checkout,label):
    s=mk(); call(s,"GET","/cart")
    call(s,"POST","/cart/add-item",{"id":10,"quantity":1})
    a=addr(country,vat)
    c,d=call(s,"POST","/cart/update-customer",{"billing_address":a,"shipping_address":{k:v for k,v in a.items() if k not in("email",)}})
    tax=d.get("totals",{}).get("total_tax") if c==200 else None
    check(f"{label}: cart total_tax={expect_tax}", tax==expect_tax, f"(got {c} {tax} {json.dumps(d)[:300]})")
    c,o=call(s,"POST","/checkout",{"billing_address":a,"shipping_address":{k:v for k,v in a.items() if k!="email"},"payment_method":"cheque"})
    check(f"{label}: checkout {'ok' if expect_checkout else 'refused'}", (c==200)==expect_checkout, f"(got {c} {json.dumps(o)[:400]})")
    return o if c==200 else None
o=scenario("DE","DE129274202","0",True,"valid DE VAT (cross-border)")
oid=o and o.get("order_id")
print("ORDER",oid)
scenario("DE","DE000000000","1900",False,"invalid DE VAT")
scenario("DE","FR40303265045","1900",False,"country mismatch")
scenario("DE",None,"1900",True,"no VAT number")
scenario("HU","HU33119856","2700",True,"domestic HU VAT (no reverse charge)")
print("FAILS",fails); sys.exit(1 if fails else 0)
