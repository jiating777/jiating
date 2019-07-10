package com.example.ihiilaptop.hello;

import cn.bmob.v3.BmobObject;

/**
 * Created by IHIILAPTOP on 2018/5/6.
 */

public class Person extends BmobObject{
    private String usr_name;
    private String usr_password;
    private int exp;//经验
    private int range;//视野范围
    private int totalTree;//已种树的数量
    private int totalTree2;//背包中树的数量
    private float mile;//累计公里数目
    private int priority;//可以种的树的数量
    private int level;//等级

    public String getUsr_name() {
        return usr_name;
    }

    public void setUsr_name(String usr_name) {
        this.usr_name = usr_name;
    }

    public String getUsr_password() {
        return usr_password;
    }

    public void setUsr_password(String usr_password) {
        this.usr_password = usr_password;
    }

    public int getExp() {
        return exp;
    }

    public void setExp(int exp) {
        this.exp = exp;
    }

    public int getRange() {
        return range;
    }

    public void setRange(int range) {
        this.range = range;
    }

    public int getTotalTree() {
        return totalTree;
    }

    public void setTotalTree(int totalTree) {
        this.totalTree = totalTree;
    }

    public int getTotalTree2() {
        return totalTree2;
    }

    public void setTotalTree2(int totalTree2) {
        this.totalTree2 = totalTree2;
    }

    public float getMile() {
        return mile;
    }

    public void setMile(float mile) {
        this.mile = mile;
    }

    public int getPriority() {
        return priority;
    }

    public void setPriority(int priority) {
        this.priority = priority;
    }

    public int getLevel() {
        return level;
    }

    public void setLevel(int level) {
        this.level = level;
    }
}
