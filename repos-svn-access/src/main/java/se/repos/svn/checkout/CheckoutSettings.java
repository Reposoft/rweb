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
 *
 * @author Staffan Olsson
 * @since 2006-apr-15
 * @version $Id$
 */
public interface CheckoutSettings {

	/**
	 * @return The local directory to check out to.
	 */
	File getWorkingCopyDirectory();
	
	/**
	 * @return The URL to checkout from
	 */
	RepositoryUrl getCheckoutUrl();
	
	/**
	 * @return What's needed for authentication and authorization
	 */
	UserCredentials getLogin();
}
