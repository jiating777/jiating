package com.example.ihiilaptop.hello;

import android.content.Intent;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.View;
import android.widget.AdapterView;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.ListView;
import android.widget.Toast;

import com.yzq.zxinglibrary.android.CaptureActivity;
import com.yzq.zxinglibrary.common.Constant;

import java.util.ArrayList;
import java.util.List;

import cn.bmob.v3.BmobQuery;
import cn.bmob.v3.exception.BmobException;
import cn.bmob.v3.listener.FindListener;
import cn.bmob.v3.listener.SaveListener;

public class StuclassActivity extends AppCompatActivity {

    private Button addClass;
    private ListView listView;
    private MyApp myApp;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_stuclass);

        listView = (ListView)findViewById(R.id.list3);
        addClass = (Button)findViewById(R.id.addClass);

        myApp = (MyApp)getApplication();
        final ArrayList<String> list2 = new ArrayList<String>();

        String studentid = myApp.getUserid();
        BmobQuery<ClassMember> bmobQuery = new BmobQuery<ClassMember>();
        bmobQuery.addWhereEqualTo("userid", studentid);
        bmobQuery.setLimit(50);
        bmobQuery.findObjects(new FindListener<ClassMember>() {
            @Override
            public void done(List<ClassMember> list, BmobException e) {
                if (e == null) {
                    for(ClassMember ac:list){
                        String str01 = ac.getClassname();
                        list2.add(str01);
                    }
                } else {
                    Toast.makeText(getApplicationContext(),e.getMessage(),Toast.LENGTH_LONG).show();
                }
            }
        });

        ArrayAdapter<String> adapter = new ArrayAdapter<String>(StuclassActivity.this, android.R.layout.simple_list_item_1,list2);
        listView.setAdapter(adapter);

        listView.setOnItemClickListener(new AdapterView.OnItemClickListener() {
            @Override
            public void onItemClick(AdapterView<?> adapterView, View view, int i, long l) {
                myApp.setClassname(list2.get(i));
                Intent intent = new Intent(StuclassActivity.this,QdsActivity.class);
                startActivity(intent);
            }
        });

        addClass.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Intent intent = new Intent(StuclassActivity.this, AddclasssActivity.class);
         /*ZxingConfig是配置类  可以设置是否显示底部布局，闪光灯，相册，是否播放提示音  震动等动能
         * 也可以不传这个参数
         * 不传的话  默认都为默认不震动  其他都为true
         * */

                //ZxingConfig config = new ZxingConfig();
                //config.setShowbottomLayout(true);//底部布局（包括闪光灯和相册）
                //config.setPlayBeep(true);//是否播放提示音
                //config.setShake(true);//是否震动
                //config.setShowAlbum(true);//是否显示相册
                //config.setShowFlashLight(true);//是否显示闪光灯
                //intent.putExtra(Constant.INTENT_ZXING_CONFIG, config);
                startActivity(intent);
            }
        });
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (resultCode == RESULT_OK) {
            if (data != null) {
                String content = data.getStringExtra(Constant.CODED_CONTENT);
                final ClassMember classMember = new ClassMember();
                classMember.setUsername(myApp.getUsername());
                classMember.setUserid(myApp.getUserid());
                BmobQuery<Class> bmobQuery = new BmobQuery<Class>();
                bmobQuery.addWhereEqualTo("fourCode",content);
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
                                        Toast.makeText(StuclassActivity.this, e.getMessage(), Toast.LENGTH_LONG).show();
                                    }
                                    else {
                                        Toast.makeText(StuclassActivity.this, "加入成功", Toast.LENGTH_LONG).show();
                                    }
                                }
                            });
                        }else{
                            Toast.makeText(StuclassActivity.this, e.getMessage(), Toast.LENGTH_SHORT).show();
                        }
                    }
                });

            }
        }
    }

}
