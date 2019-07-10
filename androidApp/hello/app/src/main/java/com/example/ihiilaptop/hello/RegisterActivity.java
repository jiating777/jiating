package com.example.ihiilaptop.hello;

import android.content.Intent;
import android.support.v7.app.ActionBar;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.util.Log;
import android.view.MenuItem;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.TextView;
import android.widget.Toast;

import java.util.List;

import cn.bmob.v3.Bmob;
import cn.bmob.v3.BmobObject;
import cn.bmob.v3.BmobQuery;
import cn.bmob.v3.datatype.BmobQueryResult;
import cn.bmob.v3.exception.BmobException;
import cn.bmob.v3.listener.FindListener;
import cn.bmob.v3.listener.QueryListener;
import cn.bmob.v3.listener.SQLQueryListener;
import cn.bmob.v3.listener.SaveListener;



public class RegisterActivity extends AppCompatActivity {

    private Button loadButton;
    private EditText nameText;
    private EditText passwordText;
    private String currentId;
    private MyApp myApp;
    private TextView loginTransfer;
    private TextView usrname;


    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_register);

        loadButton = (Button) findViewById(R.id.loadbutton);
        nameText = (EditText) findViewById(R.id.nameText);
        passwordText = (EditText) findViewById(R.id.passwordText);
        loginTransfer = (TextView) findViewById(R.id.transformLogin);

        //Bmob SDK初始化
        Bmob.initialize(this, "4f5e451401aca5790bd493ffb6d87aa8");

        ActionBar actionBar = getSupportActionBar();
        if(actionBar != null){
            actionBar.setHomeButtonEnabled(true);
            actionBar.setDisplayHomeAsUpEnabled(true);
        }

        loginTransfer.setOnClickListener(new View.OnClickListener(){
            @Override
            public void onClick(View v) {
                Intent intent = new Intent(RegisterActivity.this,LoginActivity.class);
                Bundle bundle = new Bundle();
                intent.putExtras(bundle);
                startActivity(intent);
            }
        });

        loadButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {

                final String name = nameText.getText().toString();
                final String password = passwordText.getText().toString();

                //登录
                BmobQuery<User> query = new BmobQuery<>();
                query.addWhereEqualTo("mobilePhoneNumber",name);
                query.findObjects(new FindListener<User>() {
                    @Override
                    public void done(List<User> list, BmobException e) {
                        if(e==null){
                            if(list !=null && list.size()>0){
                                if(list.get(0).getPassword().equals(password)){
                                    Toast.makeText(RegisterActivity.this, "登录成功", Toast.LENGTH_LONG).show();

                                    myApp = (MyApp)getApplication();
                                    myApp.setLoad(true);
                                    myApp.setPhone(name);
                                    myApp.setUserid(list.get(0).getObjectId());
                                    myApp.setPriority(list.get(0).getRole());
                                    myApp.setUsername(list.get(0).getUsername());
                                    myApp.setStuid(list.get(0).getStuid());
                                    //老师
                                    if(myApp.getPriority() == 0){
                                        Intent intent = new Intent(RegisterActivity.this,TeamainActivity.class);
                                        Bundle bundle = new Bundle();
                                        intent.putExtras(bundle);
                                        startActivity(intent);
                                    }
                                    //学生
                                    else{
                                        Intent intent = new Intent(RegisterActivity.this,MainActivity.class);
                                        Bundle bundle = new Bundle();
                                        intent.putExtras(bundle);
                                        startActivity(intent);
                                    }

                                }
                                else{
                                    Toast.makeText(RegisterActivity.this, "密码错误。忘记密码请联系管理员", Toast.LENGTH_LONG).show();
                                }
                            }
                            else{
                                Toast.makeText(RegisterActivity.this, "请先注册", Toast.LENGTH_LONG).show();
                            }

                        }else{
                            Toast.makeText(RegisterActivity.this, e.getMessage(), Toast.LENGTH_LONG).show();
                        }
                    }
                });

            }
        });


    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        switch (item.getItemId()) {
            case android.R.id.home:
                this.finish(); // back button
                return true;
        }
        return super.onOptionsItemSelected(item);
    }

}