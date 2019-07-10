package com.example.ihiilaptop.hello;

import android.graphics.Color;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.GridLayout;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.TextView;
import android.widget.Toast;

import java.util.ArrayList;
import java.util.List;

import cn.bmob.v3.BmobQuery;
import cn.bmob.v3.exception.BmobException;
import cn.bmob.v3.listener.FindListener;

public class AllTreeActivity extends AppCompatActivity {

    private Button button1;
    private Button button2;
    private Button button3;
    private Button button4;
    private Button button5;
    private Button button6;
    private Button button7;
    private MyApp myApp;
    private LinearLayout looo;
    private void initialG(List<MapTree> list){
        looo = (LinearLayout) findViewById(R.id.linnn);
        looo.removeAllViews();
        for(int i=0;i<list.size();i++){
            TextView textView1 = new TextView(AllTreeActivity.this);
            textView1.setTextSize(15);
            textView1.setTextColor(Color.BLACK);
            switch (list.get(i).getTreenoo()){
                case 1:
                    textView1.setText("香樟树 "+((int)(list.get(i).getTreeLocation().getLatitude()*1000))/1000.0+","+((int)(list.get(i).getTreeLocation().getLongitude()*1000))/1000.0+" "+list.get(i).getCreatedAt());
                    break;
                case 2:
                    textView1.setText("银杏树 "+((int)(list.get(i).getTreeLocation().getLatitude()*1000))/1000.0+","+((int)(list.get(i).getTreeLocation().getLongitude()*1000))/1000.0+" "+list.get(i).getCreatedAt());
                    break;
                case 3:
                    textView1.setText("红枫树 "+((int)(list.get(i).getTreeLocation().getLatitude()*1000))/1000.0+","+((int)(list.get(i).getTreeLocation().getLongitude()*1000))/1000.0+" "+list.get(i).getCreatedAt());
                    break;
                case 4:
                    textView1.setText("樱花树 "+((int)(list.get(i).getTreeLocation().getLatitude()*1000))/1000.0+","+((int)(list.get(i).getTreeLocation().getLongitude()*1000))/1000.0+" "+list.get(i).getCreatedAt());
                    break;
                case 5:
                    textView1.setText("蓝杉树 "+((int)(list.get(i).getTreeLocation().getLatitude()*1000))/1000.0+","+((int)(list.get(i).getTreeLocation().getLongitude()*1000))/1000.0+" "+list.get(i).getCreatedAt());
                    break;
                case 6:
                    textView1.setText("蓝楹树 "+((int)(list.get(i).getTreeLocation().getLatitude()*1000))/1000.0+","+((int)(list.get(i).getTreeLocation().getLongitude()*1000))/1000.0+" "+list.get(i).getCreatedAt());
                    break;
                case 7:
                    textView1.setText("圣诞树 "+((int)(list.get(i).getTreeLocation().getLatitude()*1000))/1000.0+","+((int)(list.get(i).getTreeLocation().getLongitude()*1000))/1000.0+" "+list.get(i).getCreatedAt());
                    break;
            }
            looo.addView(textView1);
            try {
                Thread.currentThread().sleep(50);
            } catch (InterruptedException e2) {
                e2.printStackTrace();
            }
        }

    }


    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_all_tree);

        button1 = (Button) findViewById(R.id.button4);
        button2 = (Button) findViewById(R.id.button5);
        button3 = (Button) findViewById(R.id.button6);
        button4 = (Button) findViewById(R.id.button7);
        button5 = (Button) findViewById(R.id.button8);
        button6 = (Button) findViewById(R.id.button9);
        button7 = (Button) findViewById(R.id.button10);

        myApp = (MyApp) getApplication();

        //显示用户每一种树的个数
        this.getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        BmobQuery<WhatTheyHave> query = new BmobQuery<>();
        query.addWhereEqualTo("uid", myApp.getUserid());
        query.findObjects(new FindListener<WhatTheyHave>() {
            @Override
            public void done(List<WhatTheyHave> list, BmobException e) {
                if (e == null) {
                    button1.setText("" + list.get(0).getTree1());
                    button2.setText("" + list.get(0).getTree2());
                    button3.setText("" + list.get(0).getTree3());
                    button4.setText("" + list.get(0).getTree4());
                    button5.setText("" + list.get(0).getTree5());
                    button6.setText("" + list.get(0).getTree6());
                    button7.setText("" + list.get(0).getTree7());
                } else {
                    Toast.makeText(getApplicationContext(), e.getMessage(), Toast.LENGTH_LONG).show();
                }
            }
        });

        //保存每一个树的信息
        final List<MapTree> list1 = new ArrayList<MapTree>();
        final List<MapTree> list2 = new ArrayList<MapTree>();
        final List<MapTree> list3 = new ArrayList<MapTree>();
        final List<MapTree> list4 = new ArrayList<MapTree>();
        final List<MapTree> list5 = new ArrayList<MapTree>();
        final List<MapTree> list6 = new ArrayList<MapTree>();
        final List<MapTree> list7 = new ArrayList<MapTree>();
        BmobQuery<MapTree> q1 = new BmobQuery<>();
        q1.addWhereEqualTo("uuid",myApp.getUserid());
        q1.findObjects(new FindListener<MapTree>() {
            @Override
            public void done(List<MapTree> list, BmobException e) {
                if(e==null){
                    for(int i = 0;i<list.size();i++){
                        switch(list.get(i).getTreenoo()){
                            case 1:
                                list1.add(list.get(i));
                                break;
                            case 2:
                                list2.add(list.get(i));
                                break;
                            case 3:
                                list3.add(list.get(i));
                                break;
                            case 4:
                                list4.add(list.get(i));
                                break;
                            case 5:
                                list5.add(list.get(i));
                                break;
                            case 6:
                                list6.add(list.get(i));
                                break;
                            case 7:
                                list7.add(list.get(i));
                                break;
                        }
                    }

                }else{
                    Toast.makeText(getApplicationContext(),e.getMessage(),Toast.LENGTH_LONG).show();
                }

            }
        });

        //按钮的点击事件1
        button1.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                initialG(list1);
            }
        });

        //按钮的点击事件2
        button2.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                initialG(list2);
            }
        });

        //按钮的点击事件3
        button3.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                initialG(list3);
            }
        });

        //按钮的点击事件4
        button4.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                initialG(list4);
            }
        });

        //按钮的点击事件5
        button5.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                initialG(list5);
            }
        });

        //按钮的点击事件6
        button6.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                initialG(list6);
            }
        });

        //按钮的点击事件7
        button7.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                initialG(list7);
            }
        });

    }

}
