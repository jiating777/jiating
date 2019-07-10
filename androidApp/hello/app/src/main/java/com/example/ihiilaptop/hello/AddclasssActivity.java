package com.example.ihiilaptop.hello;

import android.content.Intent;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;

import java.util.List;

import cn.bmob.v3.BmobQuery;
import cn.bmob.v3.exception.BmobException;
import cn.bmob.v3.listener.FindListener;
import cn.bmob.v3.listener.SaveListener;

public class AddclasssActivity extends AppCompatActivity {
    private Button button;
    private EditText editText;
    private MyApp myApp;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_addclasss);
        button = (Button)findViewById(R.id.button3);
        editText = (EditText)findViewById(R.id.editText3);
        myApp = (MyApp)getApplication();

        button.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                final String code = editText.getText().toString();
                final ClassMember classMember = new ClassMember();
                classMember.setUsername(myApp.getUsername());
                classMember.setUserid(myApp.getUserid());
                BmobQuery<Class> bmobQuery = new BmobQuery<Class>();
                bmobQuery.addWhereEqualTo("fourCode",code);
                bmobQuery.findObjects(new FindListener<Class>() {
                    @Override
                    public void done(List<Class> list, BmobException e) {
                        if(e == null){
                            classMember.setClassid(list.get(0).getObjectId());
                            classMember.setClassname(list.get(0).getName());
                            classMember.save(new SaveListener<String>() {
                                @Override
                                public void done(String s, BmobException e) {
                                    if (e != null) {
                                        Toast.makeText(AddclasssActivity.this, e.getMessage(), Toast.LENGTH_LONG).show();
                                    }
                                    else {
                                        Toast.makeText(AddclasssActivity.this, "加入成功", Toast.LENGTH_LONG).show();
                                        Intent intent = new Intent(AddclasssActivity.this,StuclassActivity.class);
                                        startActivity(intent);
                                    }
                                }
                            });
                        }else{
                            Toast.makeText(AddclasssActivity.this, e.getMessage(), Toast.LENGTH_SHORT).show();
                        }
                    }
                });
            }
        });
    }
}
