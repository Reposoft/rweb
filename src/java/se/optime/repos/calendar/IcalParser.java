/*
 * Created on 2004-okt-05
 */
package se.optime.repos.calendar;

import java.io.Reader;

import org.jical.iCalendar;
import org.w3c.dom.Document;

/**
 * @author solsson
 * @version $Id$
 */
public interface IcalParser {

    /**
     * Read calendar definition into 
     * @param iCalendarFile
     * @return contents as XML
     */
    public Document toXml(Reader iCalendarFile);
    
    /**
     * 
     * @param iCalendarFile
     * @return contents POJO
     */
    public iCalendar parseIcs(Reader iCalendarFile);
    
}
