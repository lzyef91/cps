import sys
import requests
import json

headers = {
    'Content-Type': 'application/json;charset=UTF-8',
    'Origin': 'https://scrm.qike366.com',
    'Referer': 'https://scrm.qike366.com/',
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.80 Safari/537.36'
}

def abort(status_code, msg):
    res = {'status_code':status_code, 'msg':msg}
    res = json.dumps(res, ensure_ascii=False)
    print(res)
    sys.exit()

def handle_response(res):
    if res.status_code == requests.codes.ok:
        return res
    else:
        abort(res.status_code, res.text)

def authorize(auth):
    headers.update({'Authorization':auth})

def grab_contacts(ent_id):
    """
    领取联系方式
    """
    url = 'https://api.qike366.com/api/aicustomers/enterprises/grab'
    payload = json.dumps([ent_id])
    res = requests.post(url, data=payload, headers=headers)
    res = handle_response(res)
    res = res.json()
    if res['status'] != 1:
        abort(400, 'contacts grab fail')

def get_contacts_count(ent_id):
    """
    获取联系方式的数量
    """
    url = 'https://api.qike366.com/api/aicustomers/enterpage/contacts/count'
    payload = {'entId': ent_id}
    res = requests.get(url, params=payload, headers=headers)
    res = handle_response(res)
    res = res.json()
    return res['data']

def get_contacts(ent_id, contact_type):
    """
    获取联系方式
    :params ent_id 企客企业ID
    :params contact_type 1:手机2:固话3:邮箱
    """
    url = 'https://api.qike366.com/api/aicustomers/enterpage/contacts'
    payload = {'size': 50, 'entId': ent_id, 'page': 0, 'contactType': contact_type}
    res = requests.get(url, params=payload, headers=headers)
    res = handle_response(res)
    res = res.json()
    # 构建联系人
    data = []
    for contact in res['content']:
        item = {
            'qike_contact_id': contact['contactId'],
            'contact_no': contact['contactNo'],
            'name': contact['name'],
            'duty': contact['duty'],
            'location': contact['location'],
            'source_type': contact['sources'][0]['source'],
            'source_url': contact['sources'][0]['url']
        }
        data.append(item)
    return data

def get_view_status(ent_id):
    """
    企客后台获取企业基本信息
    :param ent_id:企客企业ID
    """
    url = 'https://api.qike366.com/api/aicustomers/probe/ent/basic'
    payload = {'entId': ent_id}
    res = requests.get(url, params=payload, headers=headers)
    res = handle_response(res)
    res = res.json()

    if (len(res['data']['rows']) > 0):
        return res['data']['rows'][0]['viewStatus2Me']
    else:
        abort(404, 'not found')

def run():
    # 企客企业ID
    ent_id = sys.argv[1]

    # 授权jwt
    auth = sys.argv[2]
    authorize(auth)

    # 企客后台查询企业联系方式领取情况
    # 0：未领取 1：已领取 2：已转化 4：已锁定
    view_status = get_view_status(ent_id)

    # 无法领取联系方式
    if view_status != 0 and view_status != 1:
        abort(402, 'no authoration of contacts')

    # 获取联系方式数量
    contacts_count = get_contacts_count(ent_id)
    if contacts_count['emailContactCount'] == 0 and contacts_count['mobileContactCount'] == 0 and contacts_count['phoneContactCount'] == 0:
        abort(404, 'no contacts')

    # 领取线索
    if view_status == 0:
        grab_contacts(ent_id)

    # 获取线索
    data = {}
    # 手机
    if contacts_count['mobileContactCount'] > 0:
        data['mobile'] = get_contacts(ent_id, 1)
    else:
        data['mobile'] = []
    # 固话
    if contacts_count['phoneContactCount'] > 0:
        data['phone'] = get_contacts(ent_id, 2)
    else:
        data['phone'] = []
    # email
    if contacts_count['emailContactCount'] > 0:
        data['email'] = get_contacts(ent_id, 3)
    else:
        data['email'] = []

    out = {'status_code': 200, 'data': data}
    out = json.dumps(out, ensure_ascii=False)
    print(out)

# 非命令行运行
if __name__ == '__main__':
    run()



