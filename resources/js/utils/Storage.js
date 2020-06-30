class Storage {

    constructor (startTimeName = 'startTime') {
        this.startTimeName = startTimeName;
        this.ls = window.localStorage;
    }

    setItem (key, item) {
        if (item !== null && typeof item  === 'object') {
            if (item['expires_in']) {
                item[this.startTimeName] = (new Date().getTime() / 1000).toFixed();  // 获取当前存储时间的时间戳，并把毫秒转换为秒
            }
            item = JSON.stringify(item);  // 把json对象格式化为一个字符串
        }
        this.ls.setItem(key, item);  // 存储到localStorage里面
    }

    getItem (key) {
        var value = this.ls.getItem(key);
        try {
            value = JSON.parse(value);  // 把一个字符串解析为一个json对象
        } catch (e) {
            value = value;
        }
        if (value !== null && typeof value === 'object' && value[this.startTimeName] && value['expires_in']) {
            var currentTime = (new Date().getTime() / 1000).toFixed();
            if (currentTime - value[this.startTimeName] > value['expires_in']) {
                this.removeItem(key);
                this.removeItem('user');
                return null;
            }
        }
        return value;
    }

    removeItem (key) {
        this.ls.removeItem(key);
    }

    clear () {
        this.ls.clear();
    }

};

export default new Storage();