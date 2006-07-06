package se.repos.issu;

import static org.junit.Assert.*;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.sql.SQLException;
import java.util.Collection;
import java.util.Map;
import java.util.Set;

import javax.sql.DataSource;

import org.hibernate.SessionFactory;
import org.junit.Test;
import org.springframework.context.ApplicationContext;
import org.springframework.context.support.ClassPathXmlApplicationContext;
import org.springframework.core.io.Resource;
import org.springframework.jdbc.object.SqlUpdate;

import se.repos.issu.domain.Issue;
import se.repos.issu.persistence.IssueDao;

public class SomeTest {

	static ApplicationContext ctx = null;
	
	@org.junit.Before
	public void setUp() throws IOException {
		if (ctx == null) {
			ctx = new ClassPathXmlApplicationContext("beans.xml");
			
			// set up database
			Resource databaseSetup = ctx.getResource("database/setup.sql");
			String setupSql = getSqlFromFile(databaseSetup);
			DataSource dataSource = (DataSource) ctx.getBean("dataSource");
			runSql(setupSql, dataSource);
		}
	}

	private void runSql(String sqlStatements, DataSource dataSource) {
		new SqlUpdate(dataSource,
				sqlStatements)
				.update();
	}

	private String getSqlFromFile(Resource databaseSetup) throws IOException {
		BufferedReader in = new BufferedReader(new InputStreamReader(databaseSetup.getInputStream()));
		StringBuffer sb = new StringBuffer();
		String line = in.readLine();
		while (line != null) {
			sb.append(line).append("\n");
			line = in.readLine();
		}
		String setupSql = sb.toString();
		return setupSql;
	}
	
	@Test
	public void testContextHibernate() {
		SessionFactory sessionFactory = (SessionFactory) ctx.getBean("sessionFactory");
		assertNotNull(sessionFactory);
		assertTrue("Should be at leas one mapping configured in hibernate", 
				0 < sessionFactory.getAllClassMetadata().size());
		
		// show the known mappings
		Map classMetadata = sessionFactory.getAllClassMetadata();
		Set mappedClasses = classMetadata.keySet();
		System.out.println("--- O/R mappings ---");
		for (Object o : mappedClasses) {
			System.out.println(o + ": " + classMetadata.get(o)); 
		}
		System.out.println("--------------------");
		
		assertTrue("There should be a mapping for Issue", mappedClasses.contains(Issue.class.getName()));
	}
	
	@Test
	public void testDataSource() throws SQLException {
				
		IssueDao issueDao = (IssueDao) ctx.getBean("issueDao");
		
		Issue issue = new Issue();
		issue.setId(2L);
		issue.setName("test insert from hibernate");
		issueDao.create(issue);
		
		Collection<Issue> c = issueDao.getAll();
		assertTrue("One object should have been stored by hibernate", 1 == c.size());
	}
}
