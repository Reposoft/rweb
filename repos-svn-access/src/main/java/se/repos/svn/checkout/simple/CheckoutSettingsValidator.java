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
package se.repos.svn.checkout.simple;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import se.repos.svn.checkout.CheckoutSettings;
import se.repos.svn.file.RejectPathNotADirectory;
import se.repos.validation.Validation;
import se.repos.validation.ValidationRejectStrategy;
import se.repos.validation.strings.RejectStringContentsMissing;
import se.repos.validation.strings.RejectStringIsEmpty;

/**
 * The logic to validate settings before checkout
 *
 * @author Staffan Olsson
 * @since 2006-apr-16
 * @version $Id$
 */
public class CheckoutSettingsValidator extends ValidationRejectStrategy<CheckoutSettings> {
	
	final Logger logger = LoggerFactory.getLogger(this.getClass());
	
	@Override
	public boolean rejects(CheckoutSettings settings) {
		Validation.rule(RejectPathNotADirectory.class).validate(settings.getWorkingCopyDirectory());
		Validation.rule(new RejectStringContentsMissing("://")).validate(settings.getCheckoutUrl().toString());
		Validation.rule(RejectStringIsEmpty.class).validate(settings.getLogin().getUsername());
		Validation.rule(RejectStringIsEmpty.class).validate(settings.getLogin().getPassword());
		return false;
	}

}
