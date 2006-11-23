/* Copyright 2006 Optime data Sweden
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
package se.repos.svn.checkout;

import java.io.File;

import se.repos.svn.RepositoryUrl;
import se.repos.svn.UserCredentials;

/**
 * The information needed to check out a working copy.
 * 
 * Given the svn {@link http://svnbook.red-bean.com/nightly/en/svn.ref.svn.c.checkout.html checkout}
 * command line syntax, this defines a command<br />
 * <code>svn checkout {@see #getLogin()} {@see #getCheckoutUrl()} {@see #getWorkingCopyDirectory()}.
 * <p>
 * Normally settings should never change for a client instance. It is the settings given
 * at instantiation that will be used throught the lifetime of the client. Things like
 * the working copy path is naturally difficult to change, but also the user credentials
 * may be cached per client instance.
 * 
 * @author Staffan Olsson
 * @since 2006-apr-15
 * @version $Id$
 */
public interface CheckoutSettings {
	
	/**
	 * @return The local directory to check out to, does not end with path separator
	 */
	File getWorkingCopyFolder();
	
	/**
	 * @return The URL to checkout from, does not end with '/'
	 */
	RepositoryUrl getCheckoutUrl();
	
	/**
	 * @return What's needed for authentication and authorization
	 */
	UserCredentials getLogin();
	
	/**
	 * Converts a file system path to a relative path within the working copy.
	 * @param path The file system path.
	 * @return The path of the file in the repository, not starting with path separator.
	 *  With forward slashes, so it can be used in URL.
	 * @throws RuntimeException if the path is not inside the working copy (considered a programming error)
	 */
	String toRelative(File path);
}
