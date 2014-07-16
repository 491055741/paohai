
import cn.ikamobile.trainfinder.wlAuthenticityRealm.*;

import java.io.UnsupportedEncodingException;

import org.json.JSONException;

public class wlauth
{
    public static void main(String args[]) {
        // String wLChallengeData = "4084nhd34iq9brb2uvpbhpp21d+18.542-8.464-12.591-28.283-32.916-38.088-18.498-33.868-5.72-40.204-5.185-26.933";
        if (args.length == 0) {
            System.out.println("缺少参数ChallengeData");
            return;
        }
        String wLChallengeData = args[0];
        // System.out.println(args[0]);
        try {
            String res = WlAuthenticityRealmGenerator.getWlAuthenticityRealm(wLChallengeData);
            System.out.println(res);
        } catch (UnsupportedEncodingException e) {
            e.printStackTrace();
        } catch (JSONException e) {
            e.printStackTrace();
        }
        
        return;
    }
}
