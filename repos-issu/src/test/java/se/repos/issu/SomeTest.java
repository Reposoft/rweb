package se.repos.issu;

import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Collection;

import javax.sql.DataSource;

import org.hibernate.SessionFactory;
import org.hibernate.classic.Session;
import org.junit.Test;
import org.springframework.context.ApplicationContext;
import org.springframework.context.support.ClassPathXmlApplicationContext;
import org.springframework.jdbc.object.MappingSqlQuery;
import org.springframework.jdbc.object.SqlOperation;
import org.springframework.jdbc.object.SqlUpdate;

import se.repos.issu.domain.Issue;
import se.repos.issu.persistence.IssueDao;

import junit.framework.TestCase;

public class SomeTest {

	static ApplicationContext ctx = null;
	
	@org.junit.Before
	public void setUp() {
		if (ctx == null) ctx = new ClassPathXmlApplicationContext("beans.xml");
	}
	
	@Test
	public void testContextHibernate() {
		SessionFactory sessionFactory = (SessionFactory) ctx.getBean("sessionFactory");
		
	}
	
	@Test
	public void testDataSource() throws SQLException {
		DataSource dataSource = (DataSource) ctx.getBean("dataSource");
		dataSource.getConnection().setAutoCommit(true);
		
		new SqlUpdate(dataSource,
				"CREATE TABLE issue (" +
				"id int NOT NULL, " +
				"name varchar(100) NOT NULL)"
				).update();
		
		new SqlUpdate(dataSource,
				"INSERT INTO issue (id, name)" +
				"VALUES (1, 'testissu')")
				.update();
		
		IssueDao issueDao = (IssueDao) ctx.getBean("issueDao");
		
		Issue issue = new Issue();
		issue.setId(2L);
		issue.setName("test insert from hibernate");
		issueDao.create(issue);
		
		Collection<Issue> c = issueDao.getAll();
		System.out.println("Hibernate now finds this many objects: " + c.size());
		
//		new MappingSqlQuery(dataSource, "select id, name from issue") {
//			@Override
//			protected Object mapRow(ResultSet rs, int rowNum) throws SQLException {
//				System.out.println(rs.getLong(1) + ": " + rs.getString(2));
//				return null;
//			}
//		}.execute();
	}
}
