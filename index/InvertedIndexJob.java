import java.io.IOException;
import java.util.StringTokenizer;
import java.util.HashMap;
import java.util.*;
import org.apache.hadoop.conf.Configuration;
import org.apache.hadoop.fs.Path;
import org.apache.hadoop.io.IntWritable;
import org.apache.hadoop.io.Text;
import org.apache.hadoop.mapreduce.Job;
import org.apache.hadoop.mapreduce.Mapper;
import org.apache.hadoop.mapreduce.Reducer;
import org.apache.hadoop.mapreduce.lib.input.FileInputFormat;
import org.apache.hadoop.mapreduce.lib.output.FileOutputFormat;
public class InvertedIndexJob {
  public static class TokenizerMapper
       extends Mapper<Object, Text, Text, Text>{
    private Text word = new Text();
    public void map(Object key, Text value, Context context
                    ) throws IOException, InterruptedException {
      Text id = new Text();
      String line = value.toString();
      String[] lineArr = line.split("\t",2);
      
      id.set(lineArr[0]);
      
      StringTokenizer itr = new StringTokenizer(lineArr[1].replaceAll("[^a-zA-Z]"," ").toLowerCase().replaceAll("( )+", " ").replaceAll("\t+","t"));
      


      /*
      StringTokenizer itr = new StringTokenizer(value.toString().split("\t")[1].replaceAll("[^a-zA-Z]"," ").toLowerCase().replaceAll("( )+", " "));
      
      String line = value.toString();
      line = line.replaceAll("\t+","t");
      line = line.replaceAll("[^a-zA-Z]"," ").toLowerCase().replaceAll("( )+", " ");
      */
      
      while (itr.hasMoreTokens()) {
        word.set(itr.nextToken());
        context.write(word, id);
      }
    }
  }
  public static class SumReducer
       extends Reducer<Text,Text,Text,Text> {
    public void reduce(Text key, Iterable<Text> values,
                       Context context
                       ) throws IOException, InterruptedException {
      HashMap<String, Integer> count = new HashMap<String, Integer>();
      for (Text val : values) {
        count.put(val.toString(),count.getOrDefault(val.toString(),0)+1);
     }
      StringBuilder sb = new StringBuilder();
      Text out = new Text();
      for(String id: count.keySet()){
        sb.append(id).append(":").append(count.get(id)).append("\t");
      }
        out.set(sb.toString());
        context.write(key, out);
    }
  }
  public static void main(String[] args) throws Exception {
    Configuration conf = new Configuration();
    Job job = Job.getInstance(conf, "Inverted Index");
    job.setJarByClass(InvertedIndexJob.class);
    job.setMapperClass(TokenizerMapper.class);
    job.setReducerClass(SumReducer.class);
    job.setOutputKeyClass(Text.class);
    job.setOutputValueClass(Text.class);
    FileInputFormat.addInputPath(job, new Path(args[0]));
    FileOutputFormat.setOutputPath(job, new Path(args[1]));
    System.exit(job.waitForCompletion(true) ? 0 : 1);
  }
}