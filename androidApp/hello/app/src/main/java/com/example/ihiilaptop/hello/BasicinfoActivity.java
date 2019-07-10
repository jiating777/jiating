package com.example.ihiilaptop.hello;

import android.content.Intent;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.text.TextUtils;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.RadioButton;
import android.widget.Toast;

import cn.bmob.v3.exception.BmobException;
import cn.bmob.v3.listener.SaveListener;

public class BasicinfoActivity extends AppCompatActivity {
    private Button registerButton;
    private EditText uuusername;
    private EditText stuid;
    private EditText pppass;
    private EditText confirmP;
    private MyApp myApp;
    private RadioButton student;
    private RadioButton teacher;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_basicinfo);

        registerButton = (Button) findViewById(R.id.registerButton);
        uuusername = (EditText) findViewById(R.id.uuusername);
        stuid = (EditText) findViewById(R.id.stuid1);
        pppass = (EditText) findViewById(R.id.pppass);
        confirmP = (EditText) findViewById(R.id.confirmP);
        student = (RadioButton) findViewById(R.id.radioButton4);
        teacher = (RadioButton) findViewById(R.id.radioButton5);

        registerButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                final String name = uuusername.getText().toString();
                final String id = stuid.getText().toString();
                final String password = pppass.getText().toString();
                final String cp = confirmP.getText().toString();
                final User user = new User();
                myApp = (MyApp)getApplication();

                if (TextUtils.isEmpty(password)) {
                    Toast.makeText(BasicinfoActivity.this, "请输入密码", Toast.LENGTH_SHORT).show();
                    return;
                }
                else if(cp.equals(password)){
                    user.setMobilePhoneNumber(myApp.getPhone());
                    user.setPassword(password);
                    user.setUsername(name);
                    user.setStuid(id);

                    if(student.isChecked()){
                        user.setRole(1);
                        myApp.setPriority(1);
                    }
                    if(teacher.isChecked()){
                        user.setRole(0);
                        myApp.setPriority(0);
                    }

                    user.save(new SaveListener<String>() {
                        @Override
                        public void done(String s, BmobException e) {
                            if (e != null) {
                                Toast.makeText(BasicinfoActivity.this, e.getMessage(), Toast.LENGTH_LONG).show();
                            }
                            else {
                                myApp.setUserid(s);
                                myApp.setLoad(true);
                                myApp.setUsername(name);
                                myApp.setStuid(id);
                                Toast.makeText(BasicinfoActivity.this, "注册成功", Toast.LENGTH_LONG).show();
                            }
                        }
                    });

                    Intent intent = new Intent(BasicinfoActivity.this,RegisterActivity.class);
                    Bundle bundle = new Bundle();
                    intent.putExtras(bundle);
                    startActivity(intent);
                }
                else{
                    Toast.makeText(BasicinfoActivity.this, "两次密码输入不相符", Toast.LENGTH_SHORT).show();
                }
            }
        });
    }
}
