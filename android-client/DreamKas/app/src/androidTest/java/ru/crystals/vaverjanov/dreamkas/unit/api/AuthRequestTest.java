package ru.crystals.vaverjanov.dreamkas.unit.api;

import android.test.InstrumentationTestCase;
import android.test.suitebuilder.annotation.SmallTest;

import ru.crystals.vaverjanov.dreamkas.controller.AuthRequest;
import ru.crystals.vaverjanov.dreamkas.controller.AuthRequest_;
import ru.crystals.vaverjanov.dreamkas.controller.LighthouseRestClient_;
import ru.crystals.vaverjanov.dreamkas.controller.PreferencesManager;
import ru.crystals.vaverjanov.dreamkas.model.AuthObject;
import ru.crystals.vaverjanov.dreamkas.model.Token;

public class AuthRequestTest extends InstrumentationTestCase {
    private AuthRequest_ authRequest;

    @Override
    protected void setUp() throws Exception {
        super.setUp();

        AuthObject ao = new AuthObject("webfront_webfront", "owner@lighthouse.pro", "lighthouse", "secret");

        authRequest = AuthRequest_.getInstance_(getInstrumentation().getContext());
        authRequest.setCredentials(ao);
    }

    @SmallTest
    public void test_loginLoadDataFromNetwork() throws Exception
    {
        try {
            Thread.sleep(5000);
        } catch (InterruptedException e) {
            e.printStackTrace();
        }

        Token response = authRequest.loadDataFromNetwork();
        assertTrue(response!=null);
    }
}
