package com.example.ihiilaptop.hello;

import android.content.Intent;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.text.TextUtils;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;

import cn.bmob.v3.exception.BmobException;
import cn.bmob.v3.listener.SaveListener;

public class AddclassActivity extends AppCompatActivity {
    private EditText classname;
    private EditText location;
    private Button submit;
    private MyApp myApp;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_addclass);

        classname = (EditText) findViewById(R.id.editText);
        location = (EditText) findViewById(R.id.editText2);
        submit = (Button) findViewById(R.id.button12);

        submit.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                final String cn = classname.getText().toString();
                final String loc = location.getText().toString();
                final Class aclass = new Class();
                final String four = Integer.toString((int)(Math.random()*9000)+1000);
                myApp = (MyApp)getApplication();

                if (TextUtils.isEmpty(cn)) {
                    Toast.makeText(AddclassActivity.this, "请输入课程名", Toast.LENGTH_SHORT).show();
                    return;
                }
                if (TextUtils.isEmpty(loc)) {
                    Toast.makeText(AddclassActivity.this, "请输入上课地点", Toast.LENGTH_SHORT).show();
                    return;
                }

                aclass.setName(cn);
                aclass.setLocation(loc);
                aclass.setTeacher(myApp.getUserid());
                aclass.setFourCode(four);
                aclass.save(new SaveListener<String>() {
                    @Override
                    public void done(String s, BmobException e) {
                        if (e != null) {
                            Toast.makeText(AddclassActivity.this, e.getMessage(), Toast.LENGTH_LONG).show();
                        }
                        else {
                            Toast.makeText(AddclassActivity.this, "添加成功"+ four, Toast.LENGTH_LONG).show();
                            Intent intent = new Intent(AddclassActivity.this,TeamainActivity.class);
                            startActivity(intent);
                        }
                    }
                });
            }
        });
    }
}
