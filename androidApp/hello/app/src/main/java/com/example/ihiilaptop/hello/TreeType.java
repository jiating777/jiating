package com.example.ihiilaptop.hello;

import cn.bmob.v3.BmobObject;

/**
 * Created by IHIILAPTOP on 2018/5/7.
 */

public class TreeType extends BmobObject{
    private int treetype;
    private String treename;

    public int getTreetype() {
        return treetype;
    }

    public void setTreetype(int treetype) {
        this.treetype = treetype;
    }

    public String getTreename() {
        return treename;
    }

    public void setTreename(String treename) {
        this.treename = treename;
    }
}
