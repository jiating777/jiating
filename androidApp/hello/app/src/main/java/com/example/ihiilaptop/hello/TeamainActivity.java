package com.example.ihiilaptop.hello;

import android.content.Intent;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.View;
import android.widget.AdapterView;
import android.widget.ArrayAdapter;
import android.widget.ListView;
import android.widget.TextView;
import android.widget.Toast;

import java.util.ArrayList;
import java.util.List;

import cn.bmob.v3.Bmob;
import cn.bmob.v3.BmobQuery;
import cn.bmob.v3.exception.BmobException;
import cn.bmob.v3.listener.FindListener;

import static java.lang.Thread.sleep;

public class TeamainActivity extends AppCompatActivity {
    private TextView textView;
    private List<Class> classList=new ArrayList<>();
    private MyApp myApp;
    private ListView listview;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_teamain);

        //Bmob SDK初始化
        Bmob.initialize(this, "4f5e451401aca5790bd493ffb6d87aa8");

        textView = (TextView) findViewById(R.id.textView);

        myApp = (MyApp)getApplication();
        final ArrayList<String> list2 = new ArrayList<String>();

        String teacherid = myApp.getUserid();
        //获取老师的课程
        BmobQuery<Class> bmobQuery = new BmobQuery<Class>();
        bmobQuery.addWhereEqualTo("teacher", teacherid);
        bmobQuery.setLimit(50);
        bmobQuery.findObjects(new FindListener<Class>() {
            @Override
            public void done(List<Class> list, BmobException e) {
                if (e == null) {
                    for(Class ac:list){
                        String str01 = ac.getName();
                        list2.add(str01);
                    }
                } else {
                    Toast.makeText(getApplicationContext(),e.getMessage(),Toast.LENGTH_LONG).show();
                }
            }
        });


        ArrayAdapter<String> adapter = new ArrayAdapter<String>(TeamainActivity.this, android.R.layout.simple_list_item_1,list2);

        listview = (ListView) findViewById(R.id.list1);
        listview.setAdapter(adapter);
        listview.setOnItemClickListener(new AdapterView.OnItemClickListener() {
            @Override
            public void onItemClick(AdapterView<?> adapterView, View view, int i, long l) {
                myApp.setClassname(list2.get(i));
                Intent intent = new Intent(TeamainActivity.this,MoreclssinfoActivity.class);
                startActivity(intent);
            }
        });

        textView.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Intent intent = new Intent(TeamainActivity.this,AddclassActivity.class);
                startActivity(intent);
            }
        });

    }


}
