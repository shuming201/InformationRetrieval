import java.io.BufferedWriter;
import java.io.File;
import java.util.ArrayList;
import java.util.Arrays;
import java.io.FileInputStream;
import java.io.FileWriter;
import java.io.IOException;
import java.io.FileNotFoundException;

import org.apache.tika.exception.TikaException;
import org.apache.tika.sax.BodyContentHandler;
import org.apache.tika.metadata.Metadata;
import org.apache.tika.parser.html.HtmlParser;
import org.apache.tika.parser.ParseContext;
import org.xml.sax.SAXException;

/*      Example online
		FileInputStream inputstream = new FileInputStream(new File("./0001cdf5-6966-4649-92f2-2092d26d958f.html"));
		ParseContext pcontext = new ParseContext();

		// Html parser
		HtmlParser htmlparser = new HtmlParser();
		htmlparser.parse(inputstream, handler, metadata, pcontext);
		System.out.println("Contents of the document:" + handler.toString().replaceAll("\\s+", " "));
		writer.write(handler.toString().replaceAll("\\s+", " "));
		*/

	

public class htmlparse {
	
	public static void NewWriteFile(ArrayList<String> parsedWords) throws IOException
	{
		BufferedWriter writer = new BufferedWriter(new FileWriter("big.txt"));
		for(int m=0;m<parsedWords.size();m++)
		{
		    writer.write(parsedWords.get(m)+"\n");
		}
		//writer.close();
	}
	public static void parseFiles(String directoryPath)throws FileNotFoundException, TikaException, SAXException, IOException
	{
        File dir = new File(directoryPath);
        File[] files = dir.listFiles();
        ArrayList<String> fullList = new ArrayList();
        for(File x: files)
        {
        	fullList.addAll(parseFile(x));
        }
        NewWriteFile(fullList);
	}
	
	public static ArrayList<String> parseFile(File myFile) throws TikaException, FileNotFoundException, IOException, SAXException
	{
	      BodyContentHandler handler = new BodyContentHandler(-1);
	      FileInputStream inputstream = new FileInputStream(myFile);
	      ParseContext pcontext = new ParseContext();
	      Metadata metadata = new Metadata();
	      HtmlParser htmlparser = new HtmlParser();
	      htmlparser.parse(inputstream, handler, metadata,pcontext);
	      String finalstring = handler.toString();
	      ArrayList bigList = new ArrayList(Arrays.asList(finalstring.split("\\W+")));
	      return bigList;
	}
	
	public static void main(String args[]) throws FileNotFoundException, SAXException, IOException, TikaException 
		{
			//get the path
			String directoryPath= "C:\\Users\\Shuming\\Documents\\Data\\Reuters\\reutersnews\\reutersnews";
			parseFiles(directoryPath);
		}
}
