package com.example.ihiilaptop.hello;

import android.app.Application;

import cn.bmob.v3.datatype.BmobGeoPoint;

/**
 * Created by IHIILAPTOP on 2018/5/21.
 */

public class MyApp extends Application {
    public String userid;//用户id
    public String username;//用户名
    public boolean isLoad = false;//登录状态
    public int priority;//权限值
    public String phone;//用户电话
    public String stuid;//学号
    public String classid;//课程id
    public String classname;//课程名
    public String code;
    public BmobGeoPoint location;

    public BmobGeoPoint getLocation() {
        return location;
    }

    public void setLocation(BmobGeoPoint location) {
        this.location = location;
    }

    public String getCode() {
        return code;
    }

    public void setCode(String code) {
        this.code = code;
    }

    public String getClassname() {
        return classname;
    }

    public void setClassname(String classname) {
        this.classname = classname;
    }

    public String getClassid() {
        return classid;
    }

    public void setClassid(String classid) {
        this.classid = classid;
    }

    public String getStuid() {
        return stuid;
    }

    public void setStuid(String stuid) {
        this.stuid = stuid;
    }

    public String getPhone() {
        return phone;
    }

    public void setPhone(String phone) {
        this.phone = phone;
    }

    public String getUserid() {
        return userid;
    }

    public void setUserid(String userid) {
        this.userid = userid;
    }

    public boolean isLoad() {
        return isLoad;
    }

    public void setLoad(boolean load) {
        isLoad = load;
    }

    public String getUsername() {
        return username;
    }

    public void setUsername(String username) {
        this.username = username;
    }

    public int getPriority() {
        return priority;
    }

    public void setPriority(int priority) {
        this.priority = priority;
    }

}
