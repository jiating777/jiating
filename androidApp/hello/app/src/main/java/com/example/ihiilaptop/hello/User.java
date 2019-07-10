package com.example.ihiilaptop.hello;
import cn.bmob.v3.BmobObject;

/**
 * Created by IHIILAPTOP on 2019/6/29.
 */
public class User extends BmobObject{
    private String username;
    private String password;
    private int role;//角色
    private String mobilePhoneNumber;//手机号
    private String mobilePhoneVerified;//验证码
    private String stuid;//学号

    public String getUsername() {
        return username;
    }

    public void setUsername(String username) {
        this.username = username;
    }

    public String getPassword() {
        return password;
    }

    public void setPassword(String password) {
        this.password = password;
    }

    public int getRole() {
        return role;
    }

    public void setRole(int role) {
        this.role = role;
    }

    public String getMobilePhoneNumber() {
        return mobilePhoneNumber;
    }

    public void setMobilePhoneNumber(String mobilePhoneNumber) {
        this.mobilePhoneNumber = mobilePhoneNumber;
    }

    public String getMobilePhoneVerified() {
        return mobilePhoneVerified;
    }

    public void setMobilePhoneVerified(String mobilePhoneVerified) {
        this.mobilePhoneVerified = mobilePhoneVerified;
    }

    public String getStuid() {
        return stuid;
    }

    public void setStuid(String stuid) {
        this.stuid = stuid;
    }
}
