package com.example.ihiilaptop.hello;

import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.widget.ArrayAdapter;
import android.widget.ListView;
import android.widget.Toast;

import java.util.ArrayList;
import java.util.List;

import cn.bmob.v3.BmobQuery;
import cn.bmob.v3.exception.BmobException;
import cn.bmob.v3.listener.FindListener;

public class ClassmemberActivity extends AppCompatActivity {

    private MyApp myApp;
    private ListView listview;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_classmember);

        listview = (ListView)findViewById(R.id.list33);

        myApp = (MyApp)getApplication();
        final ArrayList<String> list2 = new ArrayList<String>();

        String classid = myApp.getClassid();
        //获取老师的课程
        BmobQuery<ClassMember> bmobQuery = new BmobQuery<ClassMember>();
        bmobQuery.addWhereEqualTo("classid", classid);
        bmobQuery.setLimit(50);
        bmobQuery.findObjects(new FindListener<ClassMember>() {
            @Override
            public void done(List<ClassMember> list, BmobException e) {
                if (e == null) {
                    for(ClassMember ac:list){
                        String str01 = ac.getUsername();
                        list2.add(str01);
                    }
                } else {
                    Toast.makeText(getApplicationContext(),e.getMessage(),Toast.LENGTH_LONG).show();
                }
            }
        });


        ArrayAdapter<String> adapter = new ArrayAdapter<String>(ClassmemberActivity.this, android.R.layout.simple_list_item_1,list2);

        listview = (ListView) findViewById(R.id.list1);
        listview.setAdapter(adapter);
    }
}
