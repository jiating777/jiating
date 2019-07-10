package com.example.ihiilaptop.hello;

import android.content.Intent;
import android.graphics.Color;
import android.support.v7.app.ActionBar;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;
import android.widget.TextView;
import android.widget.Toast;

import java.util.List;

import cn.bmob.v3.BmobQuery;
import cn.bmob.v3.exception.BmobException;
import cn.bmob.v3.listener.FindListener;
import cn.bmob.v3.listener.UpdateListener;

public class MyhonorActivity extends AppCompatActivity {
    private EditText name;
    private EditText stuid;
    private MyApp myApp;
    private Button button;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_myhonor);
        myApp = (MyApp)getApplication();
        final User u = new User();

        final EditText name = (EditText) findViewById(R.id.name);
        final EditText stuid = (EditText) findViewById(R.id.stuid1);
        Button button = (Button) findViewById(R.id.button11);
        name.setText(myApp.getUsername());
        stuid.setText(myApp.getStuid());

        button.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                u.setUsername(name.getText().toString());
                u.setStuid(stuid.getText().toString());
                u.update(myApp.getUserid(), new UpdateListener() {
                    @Override
                    public void done(BmobException e) {
                        if(e==null){
                            Toast.makeText(MyhonorActivity.this, "修改成功", Toast.LENGTH_SHORT).show();
                            Intent intent = new Intent(MyhonorActivity.this,InformationActivity.class);
                            Bundle bundle = new Bundle();
                            intent.putExtras(bundle);
                            startActivity(intent);
                        }else{
                            Toast.makeText(MyhonorActivity.this, "更新失败：" + e.getMessage(), Toast.LENGTH_SHORT).show();
                        }
                    }

                });
            }
        });

        this.getSupportActionBar().setDisplayHomeAsUpEnabled(true);

    }
}
