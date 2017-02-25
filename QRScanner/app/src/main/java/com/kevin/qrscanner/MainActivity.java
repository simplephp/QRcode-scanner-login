package com.kevin.qrscanner;

import com.uuzuche.lib_zxing.activity.CaptureActivity;
import com.uuzuche.lib_zxing.activity.ZXingLibrary;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.Toast;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.net.URL;


public class MainActivity extends Activity {

    private Button ScannerButton;

    public static final int REQUEST_CODE = 111;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);
        ZXingLibrary.initDisplayOpinion(this);

        ScannerButton = (Button) findViewById(R.id.ScannerButton);
        ScannerButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent intent = new Intent(MainActivity.this, CaptureActivity.class);
                startActivityForResult(intent, REQUEST_CODE);
            }
        });
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if(requestCode == REQUEST_CODE) {
            if (null != data) {
                final String toeknURL = data.getExtras().getString("result_string");
                try
                {
                    URL url = new URL(toeknURL);
                    StringBuilder requestTMPURL = new StringBuilder();
                    requestTMPURL.append(url.getProtocol());
                    requestTMPURL.append("://");
                    requestTMPURL.append(url.getHost());
                    requestTMPURL.append(":");
                    requestTMPURL.append(url.getDefaultPort());
                    requestTMPURL.append(url.getPath());
                    final String requestURL = requestTMPURL.toString();
                    String QueryString = url.getQuery();
                    final String username = "kevin";
                    String token = "";
                    if(!(QueryString.equals("")) || (QueryString != null)){
                        String[] sourceStrArray = QueryString.split("=");
                        token = sourceStrArray[1];
                    }
                    // 不懂android,在 MainActivity 只能异步启用网络请求(重要)
                    ScannerLogin.login(requestURL, token, username);
                }catch(IOException e)
                {
                    e.printStackTrace();
                }
            }
        }
    }

    public String bowlingJson(String player1, String player2) {
        return "{'winCondition':'HIGH_SCORE',"
                + "'name':'Bowling',"
                + "'round':4,"
                + "'lastSaved':1367702411696,"
                + "'dateStarted':1367702378785,"
                + "'players':["
                + "{'name':'" + player1 + "','history':[10,8,6,7,8],'color':-13388315,'total':39},"
                + "{'name':'" + player2 + "','history':[6,10,5,10,10],'color':-48060,'total':41}"
                + "]}";
    }
}