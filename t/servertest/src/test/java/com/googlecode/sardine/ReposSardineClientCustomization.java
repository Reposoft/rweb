package com.googlecode.sardine;

import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.params.HttpParams;
import org.apache.http.params.HttpProtocolParams;

/**
 * Must be in the same package as sardine to access the default scope 'client' field.
 */
public abstract class ReposSardineClientCustomization {

	public static void setUserAgent(Sardine client, String userAgent) {
		DefaultHttpClient httpClient = getHttpClient(client);
		HttpParams params = httpClient.getParams();
        HttpProtocolParams.setUserAgent(params, userAgent); // win xp 
	}

	private static DefaultHttpClient getHttpClient(Sardine client) {
		return ((SardineImpl) client).client;
	}
	
}
