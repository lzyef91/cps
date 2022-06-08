import sys
sys.path.append('/home/laradock/.local/lib/python3.8/site-packages')

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

def grab_contacts(ent_ids):
    """
    领取联系方式
    """
    url = 'https://api.qike366.com/api/aicustomers/enterprises/grab'
    payload = json.dumps(ent_ids)
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
    # 授权jwt
    auth = sys.argv[1]
    authorize(auth)

    # 企客企业ID
    ent_ids = sys.argv[2:]
    to_grabs = []
    to_get_contacts = []
    total_contacts_count = {}

    for ent_id in ent_ids:
        # 企客后台查询企业联系方式领取情况
        # 0：未领取 1：已领取 2：已转化 4：已锁定
        view_status = get_view_status(ent_id)

        # 无法领取联系方式则跳过
        if view_status != 0 and view_status != 1:
            continue

        # 联系方式数量为0则跳过
        contacts_count = get_contacts_count(ent_id)
        if contacts_count['emailContactCount'] == 0 and contacts_count['mobileContactCount'] == 0 and contacts_count['phoneContactCount'] == 0:
            continue

        # 待领取线索
        if view_status == 0:
            to_grabs.append(ent_id)

        # 待获取线索
        to_get_contacts.append(ent_id)
        # 线索数量
        total_contacts_count[ent_id] = contacts_count

    # print(to_grabs)
    # print(to_get_contacts)
    # print(total_contacts_count)

    # 领取线索
    if len(to_grabs) > 0:
        grab_contacts(to_grabs)

    # 获取线索
    data = []

    for ent_id in to_get_contacts:
        item = {}
        item['entid'] = ent_id

        # 手机
        if total_contacts_count[ent_id]['mobileContactCount'] > 0:
            item['mobile'] = get_contacts(ent_id, 1)
        else:
            item['mobile'] = []
        # 固话
        if total_contacts_count[ent_id]['phoneContactCount'] > 0:
            item['phone'] = get_contacts(ent_id, 2)
        else:
            item['phone'] = []
        # email
        if total_contacts_count[ent_id]['emailContactCount'] > 0:
            item['email'] = get_contacts(ent_id, 3)
        else:
            item['email'] = []

        data.append(item)

    out = {'status_code': 200, 'data': data}
    out = json.dumps(out, ensure_ascii=False)
    print(out)

# 非命令行运行
if __name__ == '__main__':
    run()



