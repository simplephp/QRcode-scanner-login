package com.kevin.qrscanner;

import java.io.IOException;

import okhttp3.FormBody;
import okhttp3.MediaType;
import okhttp3.OkHttpClient;
import okhttp3.Request;
import okhttp3.RequestBody;
import okhttp3.Response;

/**
 * Created by smart on 2017/2/22.
 */

public class ScannerLogin {

    public static final MediaType JSON = MediaType.parse("application/json; charset=utf-8");
    public static final OkHttpClient client =  new OkHttpClient();

    public static void login(final String loginURL, final String token, final String username) {
        new Thread(new Runnable() {
            public void run() {
                try {
                    FormBody body = new FormBody.Builder()
                            .add("method","thridLogin")
                            .add("token",token)
                            .add("username",username)
                            .build();
                    Request request = new Request.Builder()
                            .url(loginURL)
                            .post(body)
                            .build();
                    Response response = client.newCall(request).execute();
                    if (!response.isSuccessful()) {
                        System.out.println("网络请求结果:" + response.body().string());
                    }
                } catch (IOException e) {
                    System.out.println("网络请求异常:" + e);
                }
            }
        }).start();
    }
}
