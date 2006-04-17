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
package se.repos.validation.message;

import org.apache.commons.codec.DecoderException;
import org.apache.commons.codec.StringDecoder;
import org.springframework.context.MessageSourceResolvable;

import se.repos.tools.text.CamelCase;

/**
 * Represents invalid input to a field or method.
 * <p>
 * Unlike Exceptions that should be handled internally and recovered from, 
 * this is an unchecked exception that should be caught by the 
 * presentation or integration tier which notifies the user.
 * </p><p>
 * Designed to support the following usage patterns:
 * <ul>
 * <li>An exception hierarcy that allows exception handlers for different groups of errors.</li>
 * <li>Only runtime exception, because they deal with invalid use of API or GUI, which should be handled at the top level.</li>
 * <li>One exception per error, for example EmptyStringException, that can be thrown from several places and caught individually.</li>
 * <li>Implicit creation of i18n message code and default message, to avoid string handling in code and support convenient validation.</li>
 * </ul>
 * </p><p>
 * To construct the default names, '{@value #DEFAULT_NAME_SUFFIX}' is removed from the end of the class name if present.
 * Likewise, '{@value #DEFAULT_NAME_PREFIX}' is removed if present.
 * </p>
 * 
 * @author Staffan Olsson
 * @since 2005-nov-13
 * @version $Id$
 */
public abstract class InputException extends IllegalArgumentException
        implements MessageSourceResolvable {

    private static final long serialVersionUID = 1L;

    private static final String DEFAULT_NAME_SUFFIX = "Exception";
    
    private static final String DEFAULT_NAME_PREFIX = "Reject";
    
    /**
     * Subclasses should define constructors with runtime validation info as message parameters.
     */
    public InputException() {
    }
    
    protected InputException(Throwable cause) {
        super(cause);
    }
    
    /* (non-Javadoc)
     * @see org.springframework.context.MessageSourceResolvable#getArguments()
     */
    public abstract Object[] getArguments();
    
    /* (non-Javadoc)
     * @see java.lang.Throwable#getMessage()
     */
    @Override
    public String getMessage() {
        return getName();
    }
    
    protected String getName() {
        try {
            return getCodec().decode(getMessageCode());
        } catch (DecoderException e) {
            return getMessageCode();
        }
    }
    
    protected String getMessageCode() {
        String name = getClass().getSimpleName();
        if (name.endsWith(DEFAULT_NAME_SUFFIX)) {
            return name.substring(0, name.length() - DEFAULT_NAME_SUFFIX.length());
        } else if (name.startsWith(DEFAULT_NAME_PREFIX)) {
            return name.substring(DEFAULT_NAME_PREFIX.length());
        }
        return name;
    }
    
    protected StringDecoder getCodec() {
        return new CamelCase();
    }

    /* (non-Javadoc)
     * @see org.springframework.context.MessageSourceResolvable#getCodes()
     */
    public String[] getCodes() {
        return new String[] {getMessageCode()};
    }    

    /* (non-Javadoc)
     * @see org.springframework.context.MessageSourceResolvable#getDefaultMessage()
     */
    public String getDefaultMessage() {
        return getMessage();
    }

    /* (non-Javadoc)
     * @see java.lang.Throwable#toString()
     */
    @Override
    public String toString() {
        StringBuffer message = new StringBuffer(getMessageCode());
        addParameters(message, getArguments());
        return message.toString();
    }
    
    protected void addParameters(StringBuffer message, Object[] args) {
        for (int i = 0; i < args.length; i++) {
            message.append(' ').append('{');
            addParameter(message, args[i], i);
            message.append('}');
        }
    }

    protected void addParameter(StringBuffer message, Object arg, int paramIndex) {
        message.append(paramIndex).append(':');
        if (arg == null) {
            message.append("null");
        } else {
            message.append(arg.getClass().getSimpleName()).append('=').append(arg);
        }
    }
}
