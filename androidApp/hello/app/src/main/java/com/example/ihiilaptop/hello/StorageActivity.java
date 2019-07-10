package com.example.ihiilaptop.hello;

import android.support.v7.app.ActionBar;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.MenuItem;
import android.view.View;
import android.widget.Button;
import android.widget.TextView;
import android.widget.Toast;

import java.util.List;

import cn.bmob.v3.Bmob;
import cn.bmob.v3.BmobQuery;
import cn.bmob.v3.exception.BmobException;
import cn.bmob.v3.listener.FindListener;
import cn.bmob.v3.listener.UpdateListener;

public class StorageActivity extends AppCompatActivity {

    private MyApp myApp;
    private TextView t1;
    private TextView t2;
    private TextView t3;
    private TextView t4;
    private TextView t5;
    private TextView t6;
    private TextView t7;
    private TextView t8;
    private TextView t9;
    private TextView t10;
    private TextView t11;
    private Button b1;
    private Button b2;
    private Button b3;
    private Button b4;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_storage);

        myApp = (MyApp)getApplication();
        t1=(TextView)findViewById(R.id.smallcount);
        t2=(TextView)findViewById(R.id.farmercount);
        t3=(TextView)findViewById(R.id.bigcount);
        t4=(TextView)findViewById(R.id.jinglingcount);
        t5=(TextView)findViewById(R.id.tree1count);
        t6=(TextView)findViewById(R.id.tree2count);
        t7=(TextView)findViewById(R.id.tree3count);
        t8=(TextView)findViewById(R.id.tree4count);
        t9=(TextView)findViewById(R.id.tree5count);
        t10=(TextView)findViewById(R.id.tree6count);
        t11=(TextView)findViewById(R.id.tree7count);

        b1 = (Button)findViewById(R.id.duihuan1);//小斧头的兑换
        b2 = (Button)findViewById(R.id.hirefarmer);//老农的雇佣
        b3 = (Button)findViewById(R.id.daoqubig);//短柄斧的盗取
        b4 = (Button)findViewById(R.id.zhoahuan); //树精灵的召唤

        final BmobQuery<Person> q1 = new BmobQuery<>();
        q1.addWhereEqualTo("objectId",myApp.getUserid());

        //ActionBar 返回父页面
        this.getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        //库存显示值
        final BmobQuery<WhatTheyHave> query = new BmobQuery<>();
        query.addWhereEqualTo("uid",myApp.getUserid());
        query.findObjects(new FindListener<WhatTheyHave>() {
            @Override
            public void done(List<WhatTheyHave> list, BmobException e) {
                if(e==null){
                    if(list.size()>0){
                        t1.setText("库存 "+list.get(0).getTool1()+" 把");
                        t2.setText("库存 "+list.get(0).getTool2()+" 人");
                        t3.setText("库存 "+list.get(0).getTool3()+" 把");
                        t4.setText("库存 "+list.get(0).getTool4()+" 次");
                        t5.setText("库存 "+list.get(0).getKtree1()+" 棵");
                        t6.setText("库存 "+list.get(0).getKtree2()+" 棵");
                        t7.setText("库存 "+list.get(0).getKtree3()+" 棵");
                        t8.setText("库存 "+list.get(0).getKtree4()+" 棵");
                        t9.setText("库存 "+list.get(0).getKtree5()+" 棵");
                        t10.setText("库存 "+list.get(0).getKtree6()+" 棵");
                        t11.setText("库存 "+list.get(0).getKtree7()+" 棵");
                    }
                }else{
                    Toast.makeText(getApplicationContext(),e.getMessage(),Toast.LENGTH_LONG).show();
                }

            }
        });
        //小斧头的兑换
        b1.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                query.findObjects(new FindListener<WhatTheyHave>() {
                    @Override
                    public void done(List<WhatTheyHave> list, BmobException e) {
                        if(e==null){
                            if(list.get(0).getKtree1()>=50){
                                int tt1= list.get(0).getTool1() +1;
                                int tt5 = list.get(0).getKtree1() -50;
                                t1.setText("库存 "+tt1+" 把");
                                t5.setText("库存 "+tt5+" 棵");
                                //更新用户库存信息
                                list.get(0).setTool1(tt1);
                                list.get(0).setKtree1(tt5);
                                list.get(0).update(list.get(0).getObjectId(), new UpdateListener() {
                                    @Override
                                    public void done(BmobException e) {
                                        if(e == null){
                                        }else{
                                            Toast.makeText(getApplicationContext(),e.getMessage(),Toast.LENGTH_LONG).show();
                                        }
                                    }
                                });
                                //更新用户信息表
                                q1.findObjects(new FindListener<Person>() {
                                    @Override
                                    public void done(List<Person> list, BmobException e) {
                                        if(e==null){
                                            list.get(0).setTotalTree2(list.get(0).getTotalTree2()-50);
                                            list.get(0).update(new UpdateListener() {
                                                @Override
                                                public void done(BmobException e) {
                                                    if(e!=null)
                                                        Toast.makeText(getApplicationContext(),e.getMessage(),Toast.LENGTH_LONG).show();
                                                }
                                            });
                                        }else{
                                            Toast.makeText(getApplicationContext(),e.getMessage(),Toast.LENGTH_LONG).show();
                                        }
                                    }
                                });

                            }
                            else{
                                Toast.makeText(getApplicationContext(),"香樟树不够噢，兑换失败",Toast.LENGTH_LONG).show();
                            }

                        }else{
                            Toast.makeText(getApplicationContext(),e.getMessage(),Toast.LENGTH_LONG).show();
                        }
                    }
                });
            }
        });

        //老农的雇佣
        b2.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                query.findObjects(new FindListener<WhatTheyHave>() {
                    @Override
                    public void done(List<WhatTheyHave> list, BmobException e) {
                        if(e==null){
                            if(list.get(0).getKtree2()>=50){
                                int tt1= list.get(0).getTool2() +1;
                                int tt5 = list.get(0).getKtree2() -50;
                                t2.setText("库存 "+tt1+" 人");
                                t6.setText("库存 "+tt5+" 棵");
                                //更新用户库存信息
                                list.get(0).setTool2(tt1);
                                list.get(0).setKtree2(tt5);
                                list.get(0).update(list.get(0).getObjectId(), new UpdateListener() {
                                    @Override
                                    public void done(BmobException e) {
                                        if(e == null){
                                        }else{
                                            Toast.makeText(getApplicationContext(),e.getMessage(),Toast.LENGTH_LONG).show();
                                        }
                                    }
                                });
                                //更新用户信息表
                                q1.findObjects(new FindListener<Person>() {
                                    @Override
                                    public void done(List<Person> list, BmobException e) {
                                        if(e==null){
                                            list.get(0).setTotalTree2(list.get(0).getTotalTree2()-50);
                                            list.get(0).update(new UpdateListener() {
                                                @Override
                                                public void done(BmobException e) {
                                                    if(e!=null)
                                                        Toast.makeText(getApplicationContext(),e.getMessage(),Toast.LENGTH_LONG).show();
                                                }
                                            });
                                        }else{
                                            Toast.makeText(getApplicationContext(),e.getMessage(),Toast.LENGTH_LONG).show();
                                        }
                                    }
                                });

                            }
                            else{
                                Toast.makeText(getApplicationContext(),"银杏树不够噢，兑换失败",Toast.LENGTH_LONG).show();
                            }

                        }else{
                            Toast.makeText(getApplicationContext(),e.getMessage(),Toast.LENGTH_LONG).show();
                        }
                    }
                });
            }
        });

        //短柄斧的盗取
        b3.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                query.findObjects(new FindListener<WhatTheyHave>() {
                    @Override
                    public void done(List<WhatTheyHave> list, BmobException e) {
                        if(e==null){
                            if(list.get(0).getKtree1()>=100){
                                int tt1= list.get(0).getTool3() +1;
                                int tt5 = list.get(0).getKtree1() -100;
                                t3.setText("库存 "+tt1+" 把");
                                t5.setText("库存 "+tt5+" 棵");
                                //更新用户库存信息
                                list.get(0).setTool3(tt1);
                                list.get(0).setKtree1(tt5);
                                list.get(0).update(list.get(0).getObjectId(), new UpdateListener() {
                                    @Override
                                    public void done(BmobException e) {
                                        if(e == null){
                                        }else{
                                            Toast.makeText(getApplicationContext(),e.getMessage(),Toast.LENGTH_LONG).show();
                                        }
                                    }
                                });
                                //更新用户信息表
                                q1.findObjects(new FindListener<Person>() {
                                    @Override
                                    public void done(List<Person> list, BmobException e) {
                                        if(e==null){
                                            list.get(0).setTotalTree2(list.get(0).getTotalTree2()-100);
                                            list.get(0).update(new UpdateListener() {
                                                @Override
                                                public void done(BmobException e) {
                                                    if(e!=null)
                                                        Toast.makeText(getApplicationContext(),e.getMessage(),Toast.LENGTH_LONG).show();
                                                }
                                            });
                                        }else{
                                            Toast.makeText(getApplicationContext(),e.getMessage(),Toast.LENGTH_LONG).show();
                                        }
                                    }
                                });

                            }
                            else{
                                Toast.makeText(getApplicationContext(),"香樟树不够噢，兑换失败",Toast.LENGTH_LONG).show();
                            }

                        }else{
                            Toast.makeText(getApplicationContext(),e.getMessage(),Toast.LENGTH_LONG).show();
                        }
                    }
                });
            }
        });

        //树精灵的召唤
        b4.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                query.findObjects(new FindListener<WhatTheyHave>() {
                    @Override
                    public void done(List<WhatTheyHave> list, BmobException e) {
                        if(e==null){
                            if(list.get(0).getKtree2()>=100){
                                int tt1= list.get(0).getTool4() +1;
                                int tt5 = list.get(0).getKtree2() -100;
                                t4.setText("库存 "+tt1+" 次");
                                t6.setText("库存 "+tt5+" 棵");
                                //更新用户库存信息
                                list.get(0).setTool4(tt1);
                                list.get(0).setKtree2(tt5);
                                list.get(0).update(list.get(0).getObjectId(), new UpdateListener() {
                                    @Override
                                    public void done(BmobException e) {
                                        if(e == null){
                                        }else{
                                            Toast.makeText(getApplicationContext(),e.getMessage(),Toast.LENGTH_LONG).show();
                                        }
                                    }
                                });
                                //更新用户信息表
                                q1.findObjects(new FindListener<Person>() {
                                    @Override
                                    public void done(List<Person> list, BmobException e) {
                                        if(e==null){
                                            list.get(0).setTotalTree2(list.get(0).getTotalTree2()-100);
                                            list.get(0).update(new UpdateListener() {
                                                @Override
                                                public void done(BmobException e) {
                                                    if(e!=null)
                                                        Toast.makeText(getApplicationContext(),e.getMessage(),Toast.LENGTH_LONG).show();
                                                }
                                            });
                                        }else{
                                            Toast.makeText(getApplicationContext(),e.getMessage(),Toast.LENGTH_LONG).show();
                                        }
                                    }
                                });

                            }
                            else{
                                Toast.makeText(getApplicationContext(),"银杏不够噢，兑换失败",Toast.LENGTH_LONG).show();
                            }

                        }else{
                            Toast.makeText(getApplicationContext(),e.getMessage(),Toast.LENGTH_LONG).show();
                        }
                    }
                });
            }
        });
    }
}
