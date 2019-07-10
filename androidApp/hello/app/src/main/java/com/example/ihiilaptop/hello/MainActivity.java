package com.example.ihiilaptop.hello;

import android.content.Intent;
import android.content.pm.ApplicationInfo;
import android.content.pm.PackageManager;
import android.graphics.BitmapFactory;
import android.location.Location;
import android.media.Image;
import android.os.SystemClock;
import android.provider.SyncStateContract;
import android.support.v4.app.ActivityCompat;
import android.support.v4.content.ContextCompat;

import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.support.v7.app.WindowDecorActionBar;
import android.support.v7.widget.*;
import android.support.v7.widget.Toolbar;
import android.util.Log;
import android.view.View;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;
import android.widget.*;

import com.amap.api.location.AMapLocation;
import com.amap.api.location.AMapLocationClient;
import com.amap.api.location.AMapLocationClientOption;
import com.amap.api.location.AMapLocationListener;
import com.amap.api.maps.CameraUpdateFactory;
import com.amap.api.maps.LocationSource;
import com.amap.api.maps.MapView;
import com.amap.api.maps.AMap;
import com.amap.api.maps.AMapOptions;
import com.amap.api.maps.UiSettings;
import com.amap.api.maps.model.BitmapDescriptorFactory;
import com.amap.api.maps.model.CameraPosition;
import com.amap.api.maps.model.LatLng;
import com.amap.api.maps.model.Marker;
import com.amap.api.maps.model.MarkerOptions;
import com.amap.api.maps.model.MyLocationStyle;

import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.List;
import java.util.Map;

import cn.bmob.v3.Bmob;
import cn.bmob.v3.BmobQuery;
import cn.bmob.v3.datatype.BmobGeoPoint;
import cn.bmob.v3.exception.BmobException;
import cn.bmob.v3.listener.FindListener;
import cn.bmob.v3.listener.SaveListener;
import cn.bmob.v3.listener.UpdateListener;

import static cn.bmob.v3.Bmob.getApplicationContext;

public class MainActivity extends AppCompatActivity implements LocationSource,AMapLocationListener {

    private MapView mapView;
    private AMap aMap;
    private MarkerOptions markerOption = new MarkerOptions();
    private Button button;
    private TextView textName;
    private MyApp myApp;
    private int curpriority = 7;
    private TextView txt_first;
    private TextView txt_user;

    //定位需要的数据
    private LocationSource.OnLocationChangedListener mListener = null;
    private AMapLocationClient mLocationClient = null;
    private AMapLocationClientOption mLocationOption = null;
    private boolean isFirstLoc = true;
    private MyLocationStyle myLocationStyle;
    private float alpha = 0.7F;


    @Override
    protected void onCreate(Bundle savedInstanceState) {

        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);
        //请求权限
        if (ContextCompat.checkSelfPermission(this, android.Manifest.permission.ACCESS_COARSE_LOCATION)!= PackageManager.PERMISSION_GRANTED){
            ActivityCompat.requestPermissions(this, new String []{android.Manifest.permission.ACCESS_COARSE_LOCATION},1);
        }
        if (ContextCompat.checkSelfPermission(this, android.Manifest.permission.CAMERA)!= PackageManager.PERMISSION_GRANTED){
            ActivityCompat.requestPermissions(this, new String []{android.Manifest.permission.CAMERA},1);
        }
        if (ContextCompat.checkSelfPermission(this, android.Manifest.permission.READ_EXTERNAL_STORAGE)!= PackageManager.PERMISSION_GRANTED){
            ActivityCompat.requestPermissions(this, new String []{android.Manifest.permission.READ_EXTERNAL_STORAGE},1);
        }


        //资料
        button = (Button) findViewById(R.id.button);
        textName = (TextView)findViewById(R.id.textName);
        myApp = (MyApp)getApplication();
        txt_first = (TextView) findViewById(R.id.txt_first);
        txt_user = (TextView) findViewById(R.id.txt_user);

        txt_first.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Intent intent = new Intent(MainActivity.this, MainActivity.class);
                startActivity(intent);
            }
        });

        txt_user.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Intent intent = new Intent(MainActivity.this, StuclassActivity.class);
                startActivity(intent);
            }
        });

        if(myApp.isLoad == true){
            button.setText("资料");
            curpriority = myApp.getPriority();
            textName.setText(myApp.getUsername());
            button.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View v) {
                    //登录后只跳转资料页
                    Intent intent = new Intent(MainActivity.this, InformationActivity.class);
                    startActivity(intent);
                }
            });
        }
        else{
            button.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View v) {
                    Intent intent = new Intent(MainActivity.this, RegisterActivity.class);
                    startActivity(intent);
                }
            });
        }


        //Bmob SDK初始化
        Bmob.initialize(this, "4f5e451401aca5790bd493ffb6d87aa8");

        //初始化地图控件
        mapView =(MapView)findViewById(R.id.map);
        mapView.onCreate(savedInstanceState);
        if(aMap == null){
            myLocationStyle = new MyLocationStyle();//初始化定位蓝点样式类
            myLocationStyle.myLocationType(MyLocationStyle.LOCATION_TYPE_FOLLOW);
            myLocationStyle.interval(2000);
            aMap = mapView.getMap();
            aMap.setLocationSource(this);
            aMap.setMyLocationEnabled(true);
            aMap.setMyLocationStyle(myLocationStyle);//设置定位蓝点的Style
            UiSettings uiSettings=aMap.getUiSettings();
            uiSettings.setZoomControlsEnabled(false);
            uiSettings.setRotateGesturesEnabled(false);
            uiSettings.setMyLocationButtonEnabled(true);

        }
        location();
    }

    private void location(){
        mLocationClient = new AMapLocationClient(getApplicationContext());
        mLocationClient.setLocationListener(this);
        mLocationOption = new AMapLocationClientOption();
        mLocationOption.setLocationMode(AMapLocationClientOption.AMapLocationMode.Hight_Accuracy);
        mLocationOption.setNeedAddress(true);
        mLocationOption.setMockEnable(false);
        mLocationOption.setInterval(2000);
        mLocationClient.setLocationOption(mLocationOption);
        mLocationClient.startLocation();
    }


    @Override
    protected void onResume() {
        super.onResume();
        mapView.onResume();
    }

    @Override
    protected void onPause() {
        super.onPause();
        mapView.onPause();
    }


    @Override
    protected void onSaveInstanceState(Bundle outState) {
        super.onSaveInstanceState(outState);
        mapView.onSaveInstanceState(outState);
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        mapView.onDestroy();
        mLocationClient.stopLocation();
        mLocationClient.onDestroy();
    }


    @Override
    public void activate(OnLocationChangedListener onLocationChangedListener) {
        mListener = onLocationChangedListener;
    }

    @Override
    public void deactivate() {
        mListener = null;
    }

    @Override
    public void onLocationChanged(AMapLocation aMapLocation) {
        if (aMapLocation != null) {
            if (aMapLocation.getErrorCode() == 0) {
                //定位成功回调信息，设置相关消息
                aMapLocation.getLocationType();//获取当前定位结果来源，如网络定位结果，详见官方定位类型表
                aMapLocation.getLatitude();//获取纬度
                aMapLocation.getLongitude();//获取经度
                aMapLocation.getAccuracy();//获取精度信息
                SimpleDateFormat df = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
                Date date = new Date(aMapLocation.getTime());
                df.format(date);//定位时间
                aMapLocation.getAddress();//地址，如果option中设置isNeedAddress为false，则没有此结果，网络定位结果中会有地址信息，GPS定位不返回地址信息。
                aMapLocation.getCountry();//国家信息
                aMapLocation.getProvince();//省信息
                aMapLocation.getCity();//城市信息
                aMapLocation.getDistrict();//城区信息
                aMapLocation.getStreet();//街道信息
                aMapLocation.getStreetNum();//街道门牌号信息
                aMapLocation.getCityCode();//城市编码
                aMapLocation.getAdCode();//地区编码

                // 如果不设置标志位，此时再拖动地图时，它会不断将地图移动到当前的位置
                if (isFirstLoc) {
                    //设置缩放级别
                    aMap.moveCamera(CameraUpdateFactory.zoomTo(17));
                    //将地图移动到定位点
                    aMap.moveCamera(CameraUpdateFactory.changeLatLng(new LatLng(aMapLocation.getLatitude(), aMapLocation.getLongitude())));
                    //点击定位按钮 能够将地图的中心移动到定位点
                    mListener.onLocationChanged(aMapLocation);
                    double k = ((int) (aMapLocation.getLatitude() * 1000)) / 1000.0;//获取纬度
                    double t = ((int) (aMapLocation.getLongitude() * 1000)) / 1000.0;//获取经度

                    double m = k - 0.002;
                    double n = t - 0.002;

                    BmobQuery<MapTree> queryt = new BmobQuery<>();

                    myApp = (MyApp)getApplication();
                    isFirstLoc = false;
                }


            } else {
                //显示错误信息ErrCode是错误码，errInfo是错误信息，详见错误码表。
//                Log.e("AmapError", "location Error, ErrCode:"
//                        + aMapLocation.getErrorCode() + ", errInfo:"
//                        + aMapLocation.getErrorInfo());
//                Toast.makeText(getApplicationContext(), "定位失败"+aMapLocation.getErrorCode()+aMapLocation.getErrorInfo(), Toast.LENGTH_LONG).show();
            }
        }

    }

}