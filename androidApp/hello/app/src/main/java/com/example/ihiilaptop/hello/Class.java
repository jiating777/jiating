package com.example.ihiilaptop.hello;

import cn.bmob.v3.BmobObject;

/**
 * Created by IHIILAPTOP on 2019/7/3.
 */

public class Class extends BmobObject {
    private String fourCode;
    private String name;
    private String teacher;
    private String location;

    public String getFourCode() {
        return fourCode;
    }

    public void setFourCode(String fourCode) {
        this.fourCode = fourCode;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public String getTeacher() {
        return teacher;
    }

    public void setTeacher(String teacher) {
        this.teacher = teacher;
    }

    public String getLocation() {
        return location;
    }

    public void setLocation(String location) {
        this.location = location;
    }
}
