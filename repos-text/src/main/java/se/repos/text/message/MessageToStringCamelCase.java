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
package se.repos.text.message;

import org.apache.commons.codec.Decoder;
import org.apache.commons.codec.DecoderException;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.context.MessageSourceResolvable;

import se.repos.text.convert.CamelCase;

public class MessageToStringCamelCase implements MessageToString {

	final Logger logger = LoggerFactory.getLogger(this.getClass());
	
	private Decoder messageDecoder = new CamelCase();
	
	public String getMessage(MessageSourceResolvable message) {
		String text = message.getDefaultMessage(); 
		try {
			text = messageDecoder.decode(message.getCodes()[0]).toString();
		} catch (DecoderException e) {
			logger.error("Could not decode message '{}' using codec: {}", text, messageDecoder);
		}
		Object[] args = message.getArguments();
		if (args == null || args.length == 0) {
			return text;
		}
		return appendArgs(text, args);
	}

	/**
	 * @param text the message for no arguments
	 * @param args with at least one element
	 * @return message with arguements
	 */
	private String appendArgs(String text, Object[] args) {
		StringBuffer t = new StringBuffer(text);
		t.append(':').append(' ').append(args[0]);
		for (int i = 1; i < args.length; i++) {
			t.append(',').append(' ').append(args[i]);
		}
		return t.toString();
	}

	
	
}
