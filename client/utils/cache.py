# -*- coding:utf-8 -*-
# coding:utf-8
# 负责序列化和反序列对象
import pickle
import os

filename = "cache.db"


def save_data(data):
    output = open(filename, 'wb')
    pickle.dump(data, output)
    output.close()


def load_data():
    if os.path.getsize(filename) == 0:
        return None
    input_file = open(filename, 'rb')

    data = pickle.load(input_file)

    input_file.close()
    return data
