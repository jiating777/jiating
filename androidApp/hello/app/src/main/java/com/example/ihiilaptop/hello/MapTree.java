package com.example.ihiilaptop.hello;

import cn.bmob.v3.BmobObject;
import cn.bmob.v3.datatype.BmobGeoPoint;

/**
 * Created by IHIILAPTOP on 2018/5/9.
 */

public class MapTree extends BmobObject {
    private int treenoo;//树编号
    private boolean isEmpty = false;//是否没有树
    private boolean isResult = false;//是否结果
    private String uuid;//用户
    private boolean isProtect = false;//是否被保护；
    private BmobGeoPoint treeLocation;//位置信息


    public int getTreenoo() {
        return treenoo;
    }

    public void setTreenoo(int treenoo) {
        this.treenoo = treenoo;
    }

    public boolean isEmpty() {
        return isEmpty;
    }

    public void setEmpty(boolean empty) {
        isEmpty = empty;
    }

    public boolean isResult() {
        return isResult;
    }

    public void setResult(boolean result) {
        isResult = result;
    }

    public String getUuid() {
        return uuid;
    }

    public void setUuid(String uuid) {
        this.uuid = uuid;
    }

    public boolean isProtect() {
        return isProtect;
    }

    public void setProtect(boolean protect) {
        isProtect = protect;
    }

    public BmobGeoPoint getTreeLocation() {
        return treeLocation;
    }

    public void setTreeLocation(BmobGeoPoint treeLocation) {
        this.treeLocation = treeLocation;
    }

    public void initial(BmobGeoPoint location,String uid,int treenoo){
        this.treeLocation = location;
        this.uuid = uid;
        this.treenoo = treenoo;
    }
}
