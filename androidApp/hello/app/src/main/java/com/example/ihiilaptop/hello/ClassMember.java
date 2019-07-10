package com.example.ihiilaptop.hello;

import cn.bmob.v3.BmobObject;

/**
 * Created by IHIILAPTOP on 2019/7/5.
 */

public class ClassMember extends BmobObject {
    private int score;
    private String userid;
    private String classid;
    private String classname;
    private String username;

    public int getScore() {
        return score;
    }

    public void setScore(int score) {
        this.score = score;
    }

    public String getUserid() {
        return userid;
    }

    public void setUserid(String userid) {
        this.userid = userid;
    }

    public String getClassid() {
        return classid;
    }

    public void setClassid(String classid) {
        this.classid = classid;
    }

    public String getClassname() {
        return classname;
    }

    public void setClassname(String classname) {
        this.classname = classname;
    }

    public String getUsername() {
        return username;
    }

    public void setUsername(String username) {
        this.username = username;
    }
}
