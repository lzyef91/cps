import requests
import json
import sys

headers = {
    'Content-Type': 'application/json;charset=UTF-8',
    'Origin': 'https://scrm.qike366.com',
    'Referer': 'https://scrm.qike366.com/',
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.80 Safari/537.36'
}

def handle_response(res):
    if res.status_code == requests.codes.ok:
        return res
    else:
        res = {'status_code':res.status_code, 'msg':res.text}
        res = json.dumps(res, ensure_ascii=False)
        print(res)
        sys.exit()

def authorize(auth):
    headers.update({'Authorization':auth})

def search(principal):
    """
    企客后台搜索主体
    :param ent_id:企客企业ID
    """
    url = 'https://api.qike366.com/api/aicustomers/probe/ent/query'
    data = json.dumps({
        "current": 0,
        "grabStatus": 0,
        "intelligentSort": "intelligentSort",
        "keywords": principal,
        "queryScope": "unlimited",
        "size": 20,
        "tabType": 1
    })
    res = requests.post(url,data=data,headers=headers)
    res = handle_response(res)
    res = res.json()

    if (len(res['data']['rows']) > 0):
        return {'status_code':200, 'data': res['data']['rows'][0]}
    else:
        return {'status_code':404,'msg':'not found'}

def get_basic_info(ent_id):
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
        return {'status_code':200, 'data': res['data']['rows'][0]}
    else:
        return {'status_code':404,'msg':'not found'}

def get_business_info(ent_id):
    """
    企客后台获取企业统一社会信用代码
    :param ent_id:企客企业ID
    """
    url = 'https://api.qike366.com/api/aicustomers/enterprise/business'
    payload = {'entId': ent_id}
    res = requests.get(url, params=payload, headers=headers)
    res = handle_response(res)
    res = res.json()
    return res['enterpriseBussBaseInfoVo']['UNISCID']

if __name__ == '__main__':
    # 获取参数
    principal = sys.argv[1]
    auth = sys.argv[2]
    out = {}

    # 授权jwt
    authorize(auth)

    # 企客后台查询企业
    res = search(principal)

    if res['status_code'] == 200:
        # 查询到企业
        ent_id = res['data']['entId']
        data = {}
        data['qike_enterprise_id'] = ent_id

        # 获取基本信息
        res = get_basic_info(ent_id)
        if res['status_code'] == 200:
            # 录入基本信息
            if 'entType' in res['data']:
                data['enterprise_type'] = res['data']['entType']
            if 'entStatus' in res['data']:
                data['enterprise_status'] = res['data']['entStatus']
            if 'legalPersonName' in res['data']:
                data['legal_person_name'] = res['data']['legalPersonName']
            if 'regionCode' in res['data']:
                data['region_code'] = res['data']['regionCode']
            if 'region' in res['data']:
                data['region'] = res['data']['region']
            if 'assTag' in res['data']:
                data['size'] = res['data']['assTag']
            if 'esDate' in res['data']:
                data['established_at'] = res['data']['esDate']
            if 'contactCount' in res['data']:
                data['contact_count'] = res['data']['contactCount']
            else:
                data['contact_count'] = 0
            if 'viewStatus2Me' in res['data']:
                # 0：未领取 1：已领取 2：已转化 4：已锁定
                data['view_status'] = res['data']['viewStatus2Me']

        # 统一社会信用代码
        data['enterprise_uniscid'] = get_business_info(ent_id)

        # 输出
        out = {'status_code':200, 'data':data}
    else:
        # 查询不到企业
        out = res

    # 输出数据
    out = json.dumps(out, ensure_ascii=False)
    print(out)

