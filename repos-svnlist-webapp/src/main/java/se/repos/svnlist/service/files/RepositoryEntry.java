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
package se.repos.svnlist.service.files;

import java.io.IOException;
import java.net.URL;

public interface RepositoryEntry {
	
	/**
	 * @return The absolute URL to the repository file, also the globally unique ID of a resource
	 * TODO Skip the URLs, because of these checked exceptions
	 */
	URL getURL() throws IOException;
	
	/**
	 * @return The name to display to the user.
	 * This is usually the file or directory name, but it does not habe to be:
	 * it should simply be something that identifies the resource to the user.
	 * TODO Call it name instead. Spring resource might not be such a good idea.
	 */
	String getDescription();
}
