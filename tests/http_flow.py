"""Local integration check. Run against a disposable/local database only.

Creates unique test users and messages and leaves them available for inspection.
Requires Python requests; default URL: http://127.0.0.1:8080/mailsystem
"""
import re
import secrets
import sys
import requests

BASE = (sys.argv[1] if len(sys.argv) > 1 else 'http://127.0.0.1:8080/mailsystem').rstrip('/')
PASSWORD = secrets.token_urlsafe(18)
RUN = secrets.token_hex(4)

def check(condition, description):
    if not condition:
        raise AssertionError(description)
    print('PASS:', description)

def get(session, path):
    response = session.get(BASE + '/' + path, timeout=15)
    check(response.status_code == 200, path + ' HTTP ' + str(response.status_code) + ' (expected 200)')
    check('Fatal error' not in response.text and 'Warning:' not in response.text, path + ' no PHP errors')
    return response

def csrf(response):
    return re.search(r'name="csrf_token" value="([a-f0-9]+)"', response.text)[1]

def post(session, action, page, data, **kwargs):
    data = dict(data, csrf_token=csrf(get(session, page)))
    return session.post(BASE + '/actions/' + action + '.php', data=data, timeout=15, **kwargs)

def account(suffix):
    session = requests.Session()
    username = 'qa_' + RUN + suffix
    response = post(session, 'register', 'register.php', dict(first_name='Test', last_name=suffix, username=username, password=PASSWORD, password_repeat=PASSWORD))
    check(response.url.endswith('/login.php') and 'Hesabınız oluşturuldu' in response.text, 'registration ' + suffix)
    old_cookie = session.cookies.get('mailsystem_session')
    response = post(session, 'login', 'login.php', dict(identity=username, password=PASSWORD))
    check(response.url.endswith('/inbox.php'), 'login ' + suffix)
    check(session.cookies.get('mailsystem_session') != old_cookie, 'session ID rotated ' + suffix)
    return session, username

a, user_a = account('a')
b, user_b = account('b')
c, user_c = account('c')
check('HttpOnly' in get(requests.Session(), 'login.php').headers.get('Set-Cookie', '') and 'SameSite=Lax' in get(requests.Session(), 'login.php').headers.get('Set-Cookie', ''), 'session cookie protections')
subject = 'Integration ' + RUN
body = '<script>alert("xss")</script> Türkçe içerik'
payload = b'Local integration attachment\n'
response = post(a, 'send_message', 'compose.php', dict(recipient=user_b, subject=subject, body=body), files={'attachment': ('check.txt', payload, 'text/plain')})
check(response.url.endswith('/sent.php') and subject in response.text, 'form submission persisted in sent box')
inbox = get(b, 'inbox.php')
check(subject in inbox.text, 'message persisted in recipient inbox')
mid = re.search(r'message\.php\?id=(\d+)', inbox.text)[1]
status = b.get(BASE + '/actions/mailbox_status.php', timeout=15)
check(status.json()['unread_count'] == 1, 'JSON unread count before reading')
response = post(b, 'toggle_star', 'inbox.php', {'message_id':mid}, headers={'Accept':'application/json'})
check(response.status_code == 200 and response.json()['is_starred'] is True, 'JSON star toggle')
check(subject in get(b, 'starred.php').text, 'starred box persisted')
detail = get(b, 'message.php?id=' + mid)
check('&lt;script&gt;' in detail.text and body not in detail.text, 'stored HTML escaped')
check(b.get(BASE + '/actions/mailbox_status.php', timeout=15).json()['unread_count'] == 0, 'JSON unread count after reading')
aid = re.search(r'download\.php\?id=(\d+)', detail.text)[1]
check(b.get(BASE + '/download.php?id=' + aid, timeout=15).content == payload, 'attachment upload/download bytes match')
check(c.get(BASE + '/message.php?id=' + mid, timeout=15).status_code == 404, 'unrelated user cannot read message')
check(c.get(BASE + '/download.php?id=' + aid, timeout=15).status_code == 404, 'unrelated user cannot download attachment')
check(b.post(BASE + '/actions/toggle_star.php', data={'message_id':mid}, headers={'Accept':'application/json'}, timeout=15).status_code == 403, 'missing CSRF rejected')
check(requests.get(BASE + '/actions/mailbox_status.php', headers={'Accept':'application/json'}, timeout=15).status_code == 401, 'anonymous JSON access rejected')
response = post(b, 'language', 'inbox.php', {'language':'en', 'return_to':'inbox.php'})
check('<html lang="en">' in response.text and 'Inbox' in response.text, 'English interface')
response = post(b, 'language', 'inbox.php', {'language':'tr', 'return_to':'inbox.php'})
check('<html lang="tr">' in response.text and 'Gelen Kutusu' in response.text, 'Turkish interface')
response = post(a, 'send_message', 'compose.php', dict(recipient=user_b, subject='Invalid upload', body='Invalid'), files={'attachment': ('bad.php', b'<?php echo 1;', 'application/x-php')})
check(response.url.endswith('/compose.php') and 'Yalnızca PDF' in response.text, 'unsafe upload rejected')
check(a.get(BASE + '/storage/attachments/', timeout=15).status_code in (403, 404), 'storage directory direct access blocked')
post(b, 'delete_message', 'inbox.php', {'message_id':mid})
check(subject not in get(b, 'inbox.php').text and subject in get(b, 'trash.php').text, 'delete moves message to trash')
check(subject in get(a, 'sent.php').text, 'recipient deletion preserves sender copy')
post(a, 'delete_message', 'sent.php', {'message_id':mid})
check(post(c, 'restore_message', 'trash.php', {'message_id':mid}).status_code == 403, 'unrelated user cannot restore message')
check(b.post(BASE + '/actions/restore_message.php', data={'message_id':mid}, timeout=15).status_code == 403, 'restore requires CSRF')
check(b.get(BASE + '/actions/restore_message.php', timeout=15).status_code == 405, 'restore rejects GET')
response = post(b, 'restore_message', 'trash.php', {'message_id':mid})
check(response.url.endswith('/trash.php') and subject not in response.text, 'single restore removes message from trash')
check(subject in get(b, 'inbox.php').text, 'recipient restore returns message to inbox')
check(subject in get(a, 'trash.php').text and subject not in get(a, 'sent.php').text, 'restore preserves other user deletion')
response = post(a, 'restore_message', 'trash.php', {'message_ids[]':[mid]})
check(response.url.endswith('/trash.php') and subject in get(a, 'sent.php').text, 'bulk restore returns sender copy to sent')
check(b.get(BASE + '/download.php?id=' + aid, timeout=15).content == payload, 'restored message retains attachment')
check(subject in get(b, 'starred.php').text and b.get(BASE + '/actions/mailbox_status.php', timeout=15).json()['unread_count'] == 0, 'restore preserves star and read state')
post(b, 'logout', 'inbox.php', {})
check(get(b, 'inbox.php').url.endswith('/login.php'), 'logout revokes session access')
print('ALL HTTP CHECKS PASSED. Test user prefix: qa_' + RUN)
