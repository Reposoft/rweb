/*
 * Created on Sep 8, 2004
 */
package se.optime.repos.webdav;

import org.springframework.core.io.Resource;

/**
 * @author solsson
 * @version $Id$
 */
public interface WebRepository {

    public Resource getCurrentVersion(String fileUrl);
}
