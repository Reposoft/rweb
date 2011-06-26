package se.repos.web.servertest;

public abstract class Fixture {

	public static enum Server {
		Multirepo {
			@Override
			public String getRoot() {
				return "http://localhost:8532";
			}
		},
		Original {
			@Override
			public String getRoot() {
				return "http://localhost:8530";
			}
		};
		public abstract String getRoot();
	}
	
}
