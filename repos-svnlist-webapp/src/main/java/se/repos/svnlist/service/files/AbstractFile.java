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
import java.io.InputStream;

import org.springframework.core.io.AbstractResource;

public abstract class AbstractFile extends AbstractResource
	implements RepositoryFile {

	@Override
	public String getDescription() {
		return getFilename();
	}

	public abstract String getFilename();

	public InputStream getInputStream() throws IOException {
		throw new UnsupportedOperationException("Method AbstractFile#getInputStream not implemented.");
	}

}
