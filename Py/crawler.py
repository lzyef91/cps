import numpy as np
import requests
import json
import random
import time
import cv2
import tools
import math
import sys

scale = 0.5

headers = {
    'Content-Type': 'application/json',
    # 'Origin': 'https://passport.youzan.com',
    # 'Referer': 'https://passport.youzan.com/page/proxy',
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.80 Safari/537.36'
}


def _init_slider():
    """
    初始化滑块
    :return:
    """
    # 获取令牌
    url = 'https://passport.youzan.com/api/captcha/get-behavior-captcha-token-v2.json'

    payload = {'bizType':'32','version':'1.0'}

    result = requests.get(url, headers=headers, params=payload).json()

    token = result['data']['token']
    randomStr = result['data']['randomStr']

    # 获取验证码路径
    url = 'https://passport.youzan.com/api/captcha/get-behavior-captcha-data.json'
    payload = {'token': token,'captchaType': '1'}

    result = requests.get(url, params=payload, headers=headers).json()

    # 背景图
    bgUrl = result['data']['captchaObtainInfoResult']['bigUrl']
    # 滑块图
    sldUrl = result['data']['captchaObtainInfoResult']['smallUrl']
    cy = result['data']['captchaObtainInfoResult']['cy']

    return {
        'token': token,
        'randomStr': randomStr,
        'bgUrl': bgUrl,
        'sldUrl': sldUrl,
        'cy': cy
    }


def _pic_download(source, target='bg.jpg'):
    """
    验证码图片下载
    :param source: 图片源
    :param name: 图片本地名
    :param target: 图片本地路径
    :return:
    """

    img_data = requests.get(source, headers=headers).content

    with open(target, 'wb') as f:
        f.write(img_data)

    return target

def _cut_slider(source, target='slider_crop.jpg', threshold=2):
    """
    滑块切割
    :return:
    """

    im = cv2.imread(source)
    # 找出非透明区域的索引
    img_crop_index = np.where(im > 0)
    img_crop_index = np.array(img_crop_index)
    # 左上角裁掉的尺寸
    wc = img_crop_index[1].min()
    hc = img_crop_index[0].min()
    # 确定切块区域
    ymin = img_crop_index[0].min() - threshold
    ymax = img_crop_index[0].max() + threshold
    xmin = img_crop_index[1].min() - threshold
    xmax = img_crop_index[1].max() + threshold
    # 保存切块
    cv2.imwrite(target,im[ymin:ymax,xmin:xmax])

    return {'wc': wc ,'hc': hc,'target':target}


def _get_distance(target_path, template_path, threshold=2,  alg='can'):
    """
    获取缺口距离
    :param target_path: 匹配的目标图像路径
    :param template_path: 用于匹配的模块图像路径
    :return:
    """
    targ = 'targ_{}.jpg'.format(alg)
    temp = 'temp_{}.jpg'.format(alg)

    # 转换灰度图片
    target = cv2.imread(target_path, 0)
    template = cv2.imread(template_path, 0)
    cv2.imwrite(targ, target)
    cv2.imwrite(temp, template)

    if alg == 'can':
        # 锐化图片
        target = cv2.imread(targ)
        template = cv2.imread(temp)
        target = cv2.Canny(target, threshold1=200, threshold2=300)
        template = cv2.Canny(template, threshold1=200, threshold2=300)
        cv2.imwrite(targ, target)
        cv2.imwrite(temp, template)
    else:
        # 转换背景图颜色空间
        target = cv2.imread(targ)
        target = cv2.cvtColor(target, cv2.COLOR_BGR2GRAY)
        target = abs(255-target)
        cv2.imwrite(targ, target)

    # 读取滑块
    target = cv2.imread(targ)
    template = cv2.imread(temp)

    # 匹配
    # min_val,max_val,min_loc,max_loc = cv2.minMaxLoc(cv2.matchTemplate(target, template, cv2.TM_CCOEFF_NORMED))
    # y, x = max_loc
    res = cv2.matchTemplate(target, template, cv2.TM_CCOEFF_NORMED)
    y,x =np.unravel_index(res.argmax(), res.shape)
    # 考虑缺省值
    x += threshold
    y += threshold
    # 展示圈出来的区域
    # h, w = template.shape[:2]
    # cv2.rectangle(target, (x, y), (x + w, y + h), (7, 249, 151), 2)
    # cv2.imwrite('res_{}.jpg'.format(alg), target)

    return (x, y)


def _generate_trace(distance):
    """
    生成轨迹
    :param distance: 缺口位置的横坐标，以背景图左上角为原点
    :return:
    """
    distance = int(distance * scale) - 40
    # zx = random.randint(30, 50)
    # 初速度
    v = 0
    # 位移/轨迹列表，列表内的一个元素代表0.02s的位移
    tracks_list = []
    tracks_list.append({'mx': 40,'my': 205,'ts':'{}'.format(round(time.time()*1000))})
    # 当前的位移
    current = 0
    while current < distance - 15:
        # 加速度越小, 单位时间的位移越小, 模拟的轨迹就越多越详细
        a = random.randint(10000, 12000)  # 加速运动
        # 初速度
        v0 = v
        t = random.randint(9, 18)
        s = v0 * t / 1000 + 0.5 * a * ((t / 1000) ** 2)
        # 当前的位置
        current += s
        # 速度已经达到v, 该速度作为下次的初速度
        v = v0 + a * t / 1000
        # 添加到轨迹列表
        if current < distance:
            tracks_list.append({'mx': round(s), 'my': random.randint(0, 1),'ts': t})
    # 减速慢慢滑
    current = round(current)
    if current < distance:
        for i in range(current + 1, distance + 1):
            s = i - current
            t = random.randint(9, 18)
            tracks_list.append({'mx': s, 'my': random.randint(0, 1),'ts': t})
    else:
        for i in range(tracks_list[-1]['mx'] + 1, distance + 1):
            s = i - tracks_list[-1]['mx']
            t = random.randint(9, 18)
            tracks_list.append({'mx': s, 'my': random.randint(0, 1),'ts': t})

    return tracks_list


def aes_encrypt(random_str, data):
    """
    AES ECB模式 Pkcs7补全 加密
    :param random_str: 随机字符串
    :param data: 待加密表单
    :return:
    """
    key,iv = random_str[::-1].split('@')
    return tools.encrypt(data, key, iv)

def get_shop_info(shop_id, token):
    url = 'https://shop103695812.youzan.com/wscassets/api/shopinfo'
    payload = {'shopId':shop_id,'ticket': token}
    result = requests.get(url, headers=headers, params=payload).json()
    return result

def _slider_verify(token, encrypt_str):
    """
    验证
    :param token: 令牌
    :param encrypt_str: 加密的轨迹数据字符串
    :return:
    """
    url = 'https://passport.youzan.com/api/captcha/check-behavior-captcha-data.json'
    payload = json.dumps({
        "token": token,
        "bizType": 32,
        "bizData": "",
        "captchaType": 1,
        "userBehaviorData": encrypt_str
    })
    return requests.post(url, data=payload, headers=headers).json()


def crack(shop_id):
    # 初始化滑块
    init_data = _init_slider()

    # 下载背景
    bg = 'bg.jpg'
    _pic_download(init_data['bgUrl'], bg)
    # 下载滑块
    sld = 'sld.jpg'
    _pic_download(init_data['sldUrl'], sld)
    # 切割滑块
    sld_c = 'slider_crop.jpg'
    _cut_slider(sld, sld_c)
    # 获取缺口距离
    x, y = _get_distance(bg, sld_c)
    # 停顿 0.1 到 0.3 秒, 模拟人为操作
    time.sleep(random.uniform(0.1, 0.5))
    # 获取移动轨迹
    t = _generate_trace(x)
    # 加密数据
    data = {
        # 缺口距离
        'cx': math.ceil((x - 20) * scale),
        # 缺口上端距离
        'cy': math.ceil(init_data['cy'] * scale),
        'scale': scale,
        'slidingEvents': t
    }
    encryptStr = aes_encrypt(init_data['randomStr'], data)
    # 验证
    res = _slider_verify(init_data['token'], encryptStr)
    if (res['code'] == 0 and res['data']['success'] == True):
        info = get_shop_info(shop_id, init_data['token'])
        info = json.dumps(info['data'],ensure_ascii=False)
        print(info)


if __name__ == '__main__':
    id = int(sys.argv[1])
    crack(id)
