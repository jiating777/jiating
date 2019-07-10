package com.example.ihiilaptop.hello;

import android.content.Intent;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.media.Image;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.text.TextUtils;
import android.view.View;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.TextView;
import android.widget.Toast;

import com.google.zxing.WriterException;
import com.yzq.zxinglibrary.encode.CodeCreator;

import org.apache.http.util.EncodingUtils;
import org.w3c.dom.Text;

import java.util.List;

import cn.bmob.v3.BmobQuery;
import cn.bmob.v3.exception.BmobException;
import cn.bmob.v3.listener.FindListener;

public class MoreclssinfoActivity extends AppCompatActivity {
    private MyApp myApp;
    private String contentString;
    private ImageView qrImgImageView;
    private TextView classname;
    private TextView location;
    private TextView codes;
    private Button member;
    private Button button14;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_moreclssinfo);

        qrImgImageView = (ImageView) findViewById(R.id.imageView12);
        classname =(TextView)findViewById(R.id.classname);
        location = (TextView)findViewById(R.id.location);
        codes = (TextView)findViewById(R.id.codes);
        member = (Button)findViewById(R.id.button13);
        button14 = (Button)findViewById(R.id.button14);

        myApp = (MyApp)getApplication();
        BmobQuery<Class> bmobQuery = new BmobQuery<Class>();
        bmobQuery.addWhereEqualTo("name",myApp.getClassname());
        bmobQuery.findObjects(new FindListener<Class>() {
            @Override
            public void done(List<Class> list, BmobException e) {
                if (e == null) {
                    myApp.setClassid(list.get(0).getObjectId());
                    contentString = list.get(0).getFourCode().toString();
                    classname.setText(list.get(0).getName());
                    location.setText(list.get(0).getLocation());
                    codes.setText(contentString);
                    if (!TextUtils.isEmpty(contentString)) {
                        try{
                            Bitmap success = CodeCreator.createQRCode(contentString, 400, 400, null);
                            qrImgImageView.setImageBitmap(success);
                        } catch (WriterException e1) {
                            e1.printStackTrace();
                        }
                    } else {
                        Toast.makeText(MoreclssinfoActivity.this, "Text can not be empty", Toast.LENGTH_SHORT).show();
                    }
                } else {
                    Toast.makeText(getApplicationContext(),e.getMessage(),Toast.LENGTH_LONG).show();
                }
            }
        });

        member.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Intent intent = new Intent(MoreclssinfoActivity.this,ClassmemberActivity.class);
                startActivity(intent);
            }
        });
        button14.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Toast.makeText(getApplicationContext(),"开始签到",Toast.LENGTH_LONG).show();
            }
        });
    }
}
