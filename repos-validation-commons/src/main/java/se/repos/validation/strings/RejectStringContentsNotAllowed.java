/* Copyright 2005 Optime data Sweden
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
package se.repos.validation.strings;

/**
 * @author Staffan Olsson
 * @since 2005-nov-13
 * @version $Id$
 */
public class RejectStringContentsNotAllowed extends
        ValidateStringContentsBase {

    private static final long serialVersionUID = 1L;    

    /**
     * @param substring the string that should not be found in the value
     */
    public RejectStringContentsNotAllowed(String substring) {
        super(substring);
    }    
    
    /**
     * @param substring character that is not allowed in the value
     */
    public RejectStringContentsNotAllowed(char substring) {
        super(substring);
    }

	@Override
	protected boolean rejectsValue(String value) {
		return value.contains(getSubstring());
	}

}
