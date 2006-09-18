/* Copyright 2006 Optime data Sweden
 */
package se.repos.svn;

/**
 * The authentication of a user at a repository
 *
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public interface UserCredentials {

	public String getUsername();
	
	public String getPassword();

}
