/* $license_header$
 */
package se.repos.issu.persistence;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;

import javax.sql.DataSource;

import org.springframework.context.ApplicationContext;
import org.springframework.core.io.Resource;
import org.springframework.jdbc.object.SqlUpdate;

public class DatabaseSetup {

	public DatabaseSetup(ApplicationContext applicationContext) {
		Resource databaseSetup = applicationContext.getResource(ApplicationContext.CLASSPATH_URL_PREFIX + "database/setup.sql");
		String setupSql = getSqlFromFile(databaseSetup);
		DataSource dataSource = (DataSource) applicationContext.getBean("dataSource");
		runSql(setupSql, dataSource);
	}
	
	private static void runSql(String sqlStatements, DataSource dataSource) {
		new SqlUpdate(dataSource,
				sqlStatements)
				.update();
	}

	private static String getSqlFromFile(Resource databaseSetup) {
		StringBuffer sb = new StringBuffer(); 
		try {
		BufferedReader in = new BufferedReader(new InputStreamReader(databaseSetup.getInputStream()));
		String line = in.readLine();
		while (line != null) {
			sb.append(line).append("\n");
			line = in.readLine();
		}
		} catch (IOException e) {
			throw new RuntimeException("Could not read database setup script from resource " + databaseSetup);
		}
		String setupSql = sb.toString();
		return setupSql;
	}
}
