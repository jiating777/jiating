package com.example.ihiilaptop.hello;

import android.content.Intent;
import android.media.Image;
import android.support.v7.app.ActionBar;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.MenuItem;
import android.view.View;
import android.widget.Button;
import android.widget.ImageButton;
import android.widget.TextView;
import android.widget.Toast;

import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.List;
import java.util.TreeMap;

import cn.bmob.v3.BmobQuery;
import cn.bmob.v3.exception.BmobException;
import cn.bmob.v3.listener.FindListener;
import cn.bmob.v3.listener.QueryListener;
import cn.bmob.v3.listener.UpdateListener;

public class InformationActivity extends AppCompatActivity {

    private MyApp myApp;
    private TextView levelview;
    private Button honor;
    private Date curDate = new Date(System.currentTimeMillis());
    private Button button2;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_information);
        TextView userNameView = (TextView)findViewById(R.id.userNameView);

        final java.text.SimpleDateFormat formatter = new SimpleDateFormat( "yyyy-MM-dd HH:mm:ss");

        this.getSupportActionBar().setDisplayHomeAsUpEnabled(true);

        myApp = (MyApp)getApplication();
        userNameView.setText(myApp.getUsername());

        final BmobQuery<WhatTheyHave> q1 = new BmobQuery<>();
        q1.addWhereEqualTo("uid",myApp.getUserid());


        //跳转到修改个人信息页面
        honor = (Button)findViewById(R.id.honorbutton);
        honor.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Intent intent = new Intent(InformationActivity.this, MyhonorActivity.class);
                startActivity(intent);
            }
        });

        button2 = (Button)findViewById(R.id.button2);
        button2.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                myApp.setLoad(false);
                Intent intent = new Intent(InformationActivity.this,MainActivity.class);
                startActivity(intent);
            }
        });

    }

    public void setsomeApp(String id){
        BmobQuery<Person> query = new BmobQuery<Person>();
        query.getObject(id, new QueryListener<Person>() {
            @Override
            public void done(Person person, BmobException e) {
                if(e==null){
                    myApp = (MyApp)getApplication();
                    myApp.setPriority(person.getPriority());
                }else{
                    Toast.makeText(InformationActivity.this, e.getMessage(), Toast.LENGTH_LONG).show();
                }
            }
        });
    }

}
