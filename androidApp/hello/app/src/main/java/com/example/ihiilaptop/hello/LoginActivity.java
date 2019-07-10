package com.example.ihiilaptop.hello;

import android.content.Intent;
import android.support.v7.app.ActionBar;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.text.TextUtils;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.TextView;
import android.widget.Toast;

import cn.bmob.v3.BmobSMS;
import cn.bmob.v3.exception.BmobException;
import cn.bmob.v3.listener.QueryListener;
import cn.bmob.v3.listener.SaveListener;
import cn.bmob.v3.listener.UpdateListener;

public class LoginActivity extends AppCompatActivity {
    private Button verifyButton;
    private EditText phoneNumber;
    private Button nextStep;
    private EditText verifiedNum;
    private MyApp myApp;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);


        verifyButton = (Button) findViewById(R.id.getV);
        phoneNumber = (EditText) findViewById(R.id.phoneNumber);
        nextStep = (Button) findViewById(R.id.nextStep);
        verifiedNum = (EditText) findViewById(R.id.verifiedNumber);

        myApp = (MyApp)getApplication();

        ActionBar actionBar = getSupportActionBar();
        if(actionBar != null){
            actionBar.setHomeButtonEnabled(true);
            actionBar.setDisplayHomeAsUpEnabled(true);
        }

        verifyButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                verifyButton.setEnabled(false);
                final String phone = phoneNumber.getText().toString();
                if (TextUtils.isEmpty(phone)) {
                    Toast.makeText(LoginActivity.this, "请输入手机号码", Toast.LENGTH_SHORT).show();
                    return;
                }
                BmobSMS.requestSMSCode(phone, "login", new QueryListener<Integer>() {
                    @Override
                    public void done(Integer smsId, BmobException e) {
                        if (e == null) {
                            Toast.makeText(LoginActivity.this, "发送验证码成功，短信ID：" + smsId , Toast.LENGTH_LONG).show();
                        } else {
                            Toast.makeText(LoginActivity.this, "发送验证码失败：" + e.getErrorCode() + "-" + e.getMessage() , Toast.LENGTH_LONG).show();
                        }
                    }
                });
            }
        });

        nextStep.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                final String number = verifiedNum.getText().toString();
                final String phone = phoneNumber.getText().toString();
                if (TextUtils.isEmpty(number)) {
                    Toast.makeText(LoginActivity.this, "请输入验证码", Toast.LENGTH_SHORT).show();
                    return;
                }
                BmobSMS.verifySmsCode(phone, number, new UpdateListener() {
                    @Override
                    public void done(BmobException e) {
                        if (e == null) {
                            Toast.makeText(LoginActivity.this, "添加更多信息", Toast.LENGTH_LONG).show();
                            myApp = (MyApp)getApplication();
                            myApp.setPhone(phone);
                            Intent intent = new Intent(LoginActivity.this, BasicinfoActivity.class);
                            startActivity(intent);
                        } else {
                            Toast.makeText(LoginActivity.this, "验证码验证失败：" + e.getErrorCode() + "-" + e.getMessage(), Toast.LENGTH_SHORT).show();
                        }
                    }
                });
            }
        });
    }
}
