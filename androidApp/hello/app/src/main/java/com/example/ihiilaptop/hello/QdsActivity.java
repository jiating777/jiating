package com.example.ihiilaptop.hello;

import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.Toast;

import java.util.List;

import cn.bmob.v3.BmobQuery;
import cn.bmob.v3.exception.BmobException;
import cn.bmob.v3.listener.FindListener;
import cn.bmob.v3.listener.SaveListener;

public class QdsActivity extends AppCompatActivity {
    private Button qd;
    private MyApp myApp;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_qds);

        qd = (Button)findViewById(R.id.qd);
        myApp = (MyApp)getApplication();

        qd.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                final Qdinfo qdinfo = new Qdinfo();
                BmobQuery<ClassMember> bmobQuery = new BmobQuery<ClassMember>();
                bmobQuery.addWhereEqualTo("classname",myApp.getClassname());
                bmobQuery.findObjects(new FindListener<ClassMember>() {
                    @Override
                    public void done(List<ClassMember> list, BmobException e) {
                        if(e == null){
                            qdinfo.setClassid(list.get(0).getClassid());
                            qdinfo.setClassname(list.get(0).getClassname());
                            qdinfo.setUserid(myApp.getUserid());
                            qdinfo.setUsername(myApp.getUsername());
                            qdinfo.save(new SaveListener<String>() {
                                @Override
                                public void done(String s, BmobException e) {
                                    if(e == null){
                                        Toast.makeText(QdsActivity.this, "签到成功", Toast.LENGTH_LONG).show();
                                    } else{
                                        Toast.makeText(QdsActivity.this, e.getMessage(), Toast.LENGTH_LONG).show();
                                    }
                                }
                            });
                        }else {
                            Toast.makeText(QdsActivity.this, e.getMessage(), Toast.LENGTH_LONG).show();
                        }
                    }
                });
            }
        });
    }
}
